import $ from 'jquery';
import { ajax, dialog, observer, hooks, template } from 'peepso';
import peepsodata from 'peepsodata';

const TEMPLATE_MUTE = window.peepsomessagesdata && peepsomessagesdata.mute_confirm;

export default class MessageHeader {
	constructor(el) {
		this.id = null;

		this.$el = $(el);
		this.$avatars = this.$el.find('[data-ps=avatars]');
		this.$users = this.$el.find('[data-ps=users]');
		this.$btnOptions = this.$el.find('[data-ps=btn-options]');
		this.$dropdown = this.$el.find('[data-ps=dropdown]');
		this.$recipients = this.$el.siblings('[data-ps=recipients]');
		this.$btnAddRecipients = this.$recipients.find('[data-ps=btn-add-recipients]');
		this.$btnCancelRecipients = this.$recipients.find('[data-ps=btn-cancel-recipients]');
		this.$participants = this.$el.find('[data-ps=participants]');

		this.$btnOptions.on('click', () => this.toggleDropdown());
		this.$dropdown.on('click', () => this.toggleDropdown(false));
		this.$dropdown.on('click', '[data-menu]', e => this.onDropdownClick(e));

		hooks.addAction('messages_conversation_open', id => this.reset(id));
		hooks.addAction('messages_conversation_options', (...args) => this.updateOptions(...args));
		hooks.addAction('messages_conversation_participants', (...args) =>
			this.updateParticipants(...args)
		);
	}

	reset(id) {
		if (id === this.id) return;

		this.id = id;

		this.$avatars.empty();
		this.$users.empty();
		this.recipientsReset();
	}

	updateOptions(html) {
		this.$dropdown.html(html);
	}

	updateParticipants(html, users) {
		if (users instanceof Array) {
			if (users.length === 1) {
				let img = `<div class="ps-avatar"><img src="${users[0].avatar}"></div>`;
				this.$avatars.html(img);
				this.$users.html(html);
			} else if (users.length > 1) {
				let imgs = users.map(u => `<div class="ps-avatar"><img src="${u.avatar}"></div>`);
				this.$avatars.html(imgs.join(''));
				this.$users.html(html);
			}
		}
	}

	toggleDropdown(show) {
		show = typeof show !== 'undefined' ? !!show : this.$dropdown.is(':hidden');

		if (show) {
			this.$dropdown.show();
			setTimeout(() => {
				$(document).off('mouseup.ps-messages-header');
				$(document).on('mouseup.ps-messages-header', e => {
					let $container = this.$btnOptions.add(this.$dropdown);
					if ($container.filter(e.target).length) return;
					if ($container.find(e.target).length) return;
					this.toggleDropdown(false);
				});
			}, 1);
		} else {
			this.$dropdown.hide();
			$(document).off('mouseup.ps-messages-header');
		}
	}

	blockUser(msg, userId) {
		dialog(msg).confirm(confirmed => {
			if (confirmed) {
				window.ps_member.block_user(userId);
			}
		});
	}

	toggleReadReceipt(send, btn) {
		let params = { msg_id: this.id, read_notif: send ? 1 : 0 };

		ajax.post('messagesajax.set_message_read_notification', params).done(json => {
			if (json.success) {
				// Update button if necessary.
				if (btn instanceof Element) {
					let $btn = $(btn);
					$btn.data('send', send ? 1 : 0);
					$btn.removeClass('disabled').addClass(send ? '' : 'disabled');
					$btn.find('span').text($btn.data(`${send ? 'dontSend' : 'send'}Text`));
				}

				if (send) {
					ajax.post('messagesajax.mark_read_messages_in_conversation', {
						msg_id: this.id
					});
				}
			}
		});
	}

	toggleMute(mute, btn) {
		if (mute) {
			let popup = dialog(TEMPLATE_MUTE.replace('{msg_id}', this.id)).show();
			let $radios = popup.$el.find('input[type=radio]');
			let $btn = popup.$el.find('input[type=button]');
			$btn.removeAttr('onclick');
			$btn.on('click', e => {
				e.preventDefault();
				this.toggleMuteConfirm($radios.filter(':checked').val(), btn);
				popup.hide();
			});
		} else {
			this.toggleMuteConfirm(0, btn);
		}
	}

	toggleMuteConfirm(muteHours, btn) {
		let params = { parent_id: this.id, mute: muteHours };
		let mute = !!+muteHours;

		ajax.post('messagesajax.set_mute', params).done(json => {
			if (json.success) {
				// Update button if necessary.
				if (btn instanceof Element) {
					let $btn = $(btn);
					$btn.data('muted', mute ? 1 : 0);
					$btn.find('span').text($btn.data(`${mute ? 'muted' : 'unmuted'}Text`));
					$btn.find('i').attr('class', mute ? 'gcis gci-bell-slash' : 'gcir gci-bell');
				}

				observer.doAction(
					`psmessages_conversation_${mute ? '' : 'un'}mute`,
					params.parent_id
				);
			}
		});
	}

	leaveConversation(msg, redirect) {
		dialog(msg).confirm(confirmed => {
			if (confirmed) {
				window.location = redirect;
			}
		});
	}

	onDropdownClick(e) {
		e.preventDefault();

		let $menu = $(e.currentTarget);

		switch ($menu.data('menu')) {
			case 'block-user':
				this.blockUser($menu.data('warningText'), $menu.data('userId'));
				break;
			case 'add-recipients':
				this.recipientsToggle();
				break;
			case 'toggle-read-receipt':
				this.toggleReadReceipt(!+$menu.data('send'), $menu[0]);
				break;
			case 'toggle-mute':
				this.toggleMute(!+$menu.data('muted'), $menu[0]);
				break;
			case 'leave-conversation':
				this.leaveConversation($menu.data('warningText'), $menu.attr('href'));
				break;
		}
	}

	recipientsToggle() {
		this.$recipients.slideDown();
		this.recipientsInit();
	}

	recipientsInit() {
		let $select = this.$recipients.find('select[name=recipients]');
		if ($select[0].selectize) return;

		let avatars = {};

		$select.selectize({
			valueField: 'id',
			labelField: 'display_name',
			searchField: 'display_name',
			plugins: ['remove_button'],
			closeAfterSelect: true,
			render: {
				option(item, escape) {
					let name = escape(item.display_name);
					let avatar = escape(item.avatar || avatars[item.id] || '');
					return `<div><img src="${avatar}" /><span>${name}</span></div>`;
				},
				item(item, escape) {
					let name = escape(item.display_name);
					let avatar = escape(item.avatar || avatars[item.id] || '');
					return `<div><img src="${avatar}" /><span>${name}</span></div>`;
				}
			},
			load: (query, callback) => {
				let params = { parent_id: this.id, keyword: query };
				ajax.post('messagesajax.get_available_recipients', params).done(json => {
					if (json.success) {
						let recipients = json.data.available_participants || [];
						$.each(recipients, (id, user) => (avatars[user.id] = user.avatar)); // update avatars cache
						return callback(recipients);
					} else {
						return callback();
					}
				});
			},
			onInitialize() {
				this.$wrapper.append(`<img src="${this.$input.data('loading')}" />`);
				this.$control_input.on('input', e =>
					setTimeout(() => $(e.currentTarget).trigger('keyup'), 0)
				);
			}
		});

		this.$btnAddRecipients.on('click', () => this.recipientsAdd());
		this.$btnCancelRecipients.on('click', () => this.recipientsCancel());
	}

	recipientsReset() {
		this.$recipients.hide();
		this.$btnAddRecipients.off('click');
		this.$btnCancelRecipients.off('click');

		let $select = this.$recipients.find('select[name=recipients]');
		if ($select[0].selectize) {
			$select[0].selectize.destroy();
			delete $select[0].selectize;
		}
	}

	recipientsAdd() {
		let $select = this.$recipients.find('select[name=recipients]');
		let $nonce = this.$recipients.find('select[name=add-participant-nonce]');

		let params = {
			parent_id: this.id,
			participants: $select.val(),
			add_participant_nonce: $nonce.val()
		};

		ajax.post('messagesajax.add_participants', params).done(json => {
			if (json.success) {
				let redirect = json.data.new_conversation_redirect;
				if (redirect) {
					window.location = redirect;
					window.location.reload();
					return;
				}

				hooks.doAction(
					'messages_conversation_participants',
					json.data.summary,
					json.data.users
				);

				dialog(json.notices[0]).show().autohide();

				// Update selectbox options.
				$select.find('option').remove();
				$.each(json.data.available_participants, (id, user) => {
					let $option = $('<option/>').val(user.id).text(user.display_name);
					$select.append($option);
				});

				$select[0].selectize.clearOptions(true);

				this.$recipients.slideUp();
			}
		});
	}

	recipientsCancel() {
		this.$recipients.hide();
	}
}
