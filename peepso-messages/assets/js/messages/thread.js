import $ from 'jquery';
import { observer, hooks, template } from 'peepso';
import {
	ajaxurl_legacy as AJAX_URL,
	peepso_nonce as AJAX_NONCE,
	notification_ajax_delay_min as POLLING_DELAY_MIN,
	notification_ajax_delay as POLLING_DELAY_MAX,
	notification_ajax_delay_multiplier as POLLING_DELAY_MULTIPLIER
} from 'peepsodata';
import { filterMessages, currentlyTyping, loadAsyncContents } from './util';

export default class MessageThread {
	constructor(el) {
		this.id = null;

		this.$el = $(el);
		this.$loading = this.$el.find('[data-ps=loading]');
		this.$items = this.$el.find('[data-ps=items]');
		this.$temporary = this.$el.find('[data-ps=temporary]');
		this.$typing = this.$el.find('[data-ps=typing]');

		// Item template.
		let tmplItem = document.querySelector('[data-template=message-item]');
		this.tmplItem = template(tmplItem.innerText);

		hooks.addAction('messages_conversation_open', id => this.reset(id));
		hooks.addAction('messages_conversation_before_send', data => this.actionBeforeSend(data));
		hooks.addAction('messages_conversation_sent', data => this.actionSent(data));
	}

	reset(id) {
		if (id === this.id) return;

		this.id = id;

		this.$loading.hide();
		this.$items.empty();
		this.$temporary.empty();
		this.$typing.hide();

		this.stopLongPolling();
		this.load();
	}

	fetch(data = {}) {
		let defer = $.Deferred();
		let xhr = $.ajax({
			type: 'POST',
			dataType: 'json',
			beforeSend: xhr => xhr.setRequestHeader('X-PeepSo-Nonce', AJAX_NONCE),
			url: `${AJAX_URL}messagesajax.get_messages_in_conversation?new`,
			data
		});

		// Make sure requests are abortable.
		defer.abort = xhr.abort;

		xhr.done(json => {
			if (json.success) {
				defer.resolve(json.data);
			} else if (json.errors && data.page === 1) {
				defer.reject(json.errors);
			} else {
				defer.resolve({});
			}
		}).fail(() => defer.reject());

		return defer;
	}

	load() {
		let params = {
			msg_id: this.id,
			get_participants: 1,
			get_options: 1,
			get_unread: 1
		};

		this.$items.empty();
		this.$loading.show();

		return this.fetch(params)
			.done(data => {
				this.render(data);

				// Set initial "Enter to send" checkbox state.
				if ('undefined' !== typeof data.enter_to_send) {
					hooks.doAction('messages_conversation_enter_to_send', !!+data.enter_to_send);
				}

				this.startLongPolling();

				// Delay loading previous messages a bit.
				setTimeout(() => this.maybeLoadPrevious(), 2000);
			})
			.always(() => this.$loading.hide());
	}

	maybeLoadPrevious() {
		let evtName = 'scroll.ps-message-thread';

		this.$el.off(evtName);
		this.$el.on(evtName, () => {
			if (this.$el[0].scrollTop < 30) {
				this.$el.off(evtName);
				this.loadPrevious().done(data => {
					if (data.html) this.maybeLoadPrevious();
				});
			}
		});
	}

	loadPrevious() {
		let $first = this.$items.children('.ps-js-message').first();

		let params = {
			msg_id: this.id,
			from_id: $first.data('id'),
			direction: 'old',
			get_unread: 1
		};

		this.$loading.show();

		this.loadPrevXhr && this.loadPrevXhr.abort();
		this.loadPrevXhr = this.fetch(params);

		return this.loadPrevXhr
			.done(data => {
				this.render(data, 'prepend');
				this.scrollTo('top');
			})
			.always(() => {
				this.$loading.hide();
				delete this.loadPrevXhr;
			});
	}

	loadNext() {
		let $last = this.$items.children('.ps-js-message').last();

		let params = {
			msg_id: this.id,
			from_id: $last.data('id'),
			direction: 'new',
			get_unread: 1
		};

		this.loadNextXhr && this.loadNextXhr.abort();
		this.loadNextXhr = this.fetch(params);

		return this.loadNextXhr
			.done(data => {
				let $temporary = this.$temporary.children('.ps-js-temporary-message');
				if (data.ids && data.ids.length) {
					$temporary = $temporary.slice(0, data.ids.length);
				}

				$temporary.remove();

				this.render(data);
				this.scrollTo('bottom');

				// Restart polling if necessary.
				if (this.getTypingHtml(data) || (data.ids && data.ids.length)) {
					this.restartLongPolling();
				}
			})
			.always(() => delete this.loadNextXhr);
	}

	render(data, method = 'append') {
		if (data.html) {
			let $filtered = filterMessages($(data.html));
			this.$items[method === 'prepend' ? 'prepend' : 'append']($filtered);
			this.scrollTo(method === 'prepend' ? 'top' : 'bottom');
		}

		let typing = this.getTypingHtml(data);
		typing ? this.$typing.html(typing).show() : this.$typing.hide().empty();

		if (method === 'append' && (data.html || typing)) {
			requestAnimationFrame(() => {
				this.scrollTo('bottom');
				// Scroll again after async contents are all loaded.
				loadAsyncContents(data.html || '').then(() => this.scrollTo('bottom'));
			});
		}

		// Update read/unread checkmarks if enabled.
		if ('undefined' !== typeof data.receipt) {
			let sendReceipt = +data.receipt;
			if (sendReceipt) {
				let unreadCount = +data.unread || 0;
				this.showUnreadCheckmark(unreadCount);
			}
		}

		if (data.html_participants) {
			hooks.doAction(
				'messages_conversation_participants',
				data.html_participants,
				data.users
			);
		}

		if (data.html_options) {
			hooks.doAction('messages_conversation_options', data.html_options);
		}
	}

	showUnreadCheckmark(unreadCount) {
		let $readCheckmarks = this.$items.find('.gci-check-circle'),
			$unreadCheckmarks = $();

		if (unreadCount > 0) {
			$unreadCheckmarks = $readCheckmarks.slice(0 - unreadCount);
			$readCheckmarks = $readCheckmarks.not($unreadCheckmarks);
		}

		$readCheckmarks.addClass('read');
		$unreadCheckmarks.removeClass('read');
	}

	actionBeforeSend(data) {
		let attachment;

		if ('photo' === data.type) {
			attachment = { type: 'photo', count: data.files.length };
		} else if ('giphy' === data.type) {
			attachment = { type: 'giphy', count: 1 };
		}

		// Adds temporary content.
		this.$temporary.append(this.tmplItem({ content: data.content, attachment }));

		this.scrollTo('bottom');
	}

	actionSent() {
		this.loadNext();
	}

	scrollTo(to) {
		if (to === 'bottom') {
			this.$el[0].scrollTop = this.$el[0].scrollHeight;
		} else if (to === 'top') {
			this.$el[0].scrollTop = 0;
		}
	}

	startLongPolling() {
		this.longPollingToken = new Date().getTime();

		let looperToken = this.longPollingToken;
		let looper = delay => {
			this.longPollingTimer = setTimeout(() => {
				this.loadNext().always(() => {
					if (this.destroyed) {
						console.log(
							`Requested conversation thread (${this.params.msg_id}) is no longer exist. ` +
								`Terminate corresponding long polling loop!`
						);
					} else if (looperToken !== this.longPollingToken) {
						console.log(`Different token. Terminate corresponding long polling loop!`);
					} else {
						// Check whether current conversation is still exist.
						let nextDelay = Math.min(
							+POLLING_DELAY_MULTIPLIER * delay,
							+POLLING_DELAY_MAX
						);
						looper(nextDelay);
					}
				});
			}, delay);
		};

		looper(+POLLING_DELAY_MIN);
	}

	stopLongPolling() {
		clearTimeout(this.longPollingTimer);
		this.longPollingToken = null;
	}

	restartLongPolling() {
		this.stopLongPolling();
		this.startLongPolling();
	}

	getTypingHtml(data) {
		return !!data.currently_typing && !(data.ids && data.ids.length)
			? data.currently_typing
			: '';
	}
}
