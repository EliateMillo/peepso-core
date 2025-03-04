import $ from 'jquery';
import { throttle } from 'underscore';
import { ajax, observer, hooks } from 'peepso';
import peepsodata from 'peepsodata';
import { currentlyTyping } from './util';

import './messagebox-option-abstract';
import './messagebox-option-moods';
import './messagebox-option-location';
import './messagebox-type-abstract';
import './messagebox-type-text';

let AJAX_URL = peepsodata.ajaxurl_legacy;
let AJAX_NONCE = peepsodata.peepso_nonce;
let USER_ID = peepsodata.currentuserid;
let VIEWED_USER_ID = peepsodata.userid;
let CONTENT_MAX_LENGTH = window.peepsomessagesdata && peepsomessagesdata.character_limit;

export default class Messagebox {
	constructor(el) {
		this.id = null;

		this.$el = $(el);
		this.$titleExtra = this.$el.find('[data-ps=title-extra]');
		this.$textarea = this.$el.find('textarea').attr('maxlength', CONTENT_MAX_LENGTH);
		this.$btnCancel = this.$el.find('[data-ps=btn-cancel]').hide();
		this.$btnSubmit = this.$el.find('[data-ps=btn-submit]').hide();

		this.$textarea.on('focus click', throttle(() => this.onTextareaFocus(), 2000).bind(this));
		this.$textarea.on('input', e => this.onTextareaInput(e));
		this.$textarea.on('paste', e => setTimeout(() => this.onTextareaInput(e), 100));
		this.$btnCancel.on('click', e => this.onBtnCancelClick(e));
		this.$btnSubmit.on('click', e => this.onBtnSubmitClick(e));

		hooks.addAction('messages_conversation_open', id => this.reset(id));
		hooks.addAction('messages_conversation_enter_to_send', y => this.initEnterToSend(y));

		hooks.doAction('messagebox_init', this);
	}

	render() {
		let data = this.data();
		let title = hooks.applyFilters('messagebox_title_extra', [], data, this);

		title.length
			? this.$titleExtra.html(`${title.join(' ')}.`).show()
			: this.$titleExtra.empty().hide();
	}

	data() {
		let data = {
			content: this.$textarea.val().trim(),
			id: USER_ID,
			uid: VIEWED_USER_ID,
			type: 'activity',
			parent_id: this.id
		};

		return hooks.applyFilters('messagebox_data', data, this);
	}

	reset(id = null) {
		this.id = id || this.id;

		this.$textarea.val('').ps_autosize();
		this.$btnCancel.add(this.$btnSubmit).hide();

		hooks.doAction('messagebox_reset', this);
	}

	cancel() {
		return $.Deferred(defer => {
			this.reset();
			defer.resolve();
		});
	}

	submit() {
		return $.Deferred(defer => {
			if (this.submitting) return defer.reject();

			this.submitting = true;
			this.$btnSubmit.addClass('pso-btn--loading');

			let params = {
				url: `${AJAX_URL}messagesajax.add_message`,
				type: 'POST',
				data: this.data(),
				dataType: 'json',
				beforeSend: xhr => xhr.setRequestHeader('X-PeepSo-Nonce', AJAX_NONCE)
			};

			hooks.doAction('messages_conversation_before_send', params.data);

			let xhr = $.ajax(params);
			xhr.fail(defer.reject);
			xhr.done(json => {
				if (json.success) {
					hooks.doAction('messagebox_saved', this);
					hooks.doAction('messages_conversation_sent', params.data);
					this.reset();
					defer.resolve();
				} else {
					defer.reject();
				}
			});
			xhr.always(() => {
				delete this.submitting;
				this.$btnSubmit.removeClass('pso-btn--loading');
			});
		});
	}

	onTextareaFocus(e) {
		ajax.post('messagesajax.mark_read_messages_in_conversation?box', { msg_id: this.id });
	}

	onTextareaInput() {
		let data = this.data();
		let content = data.content.trim();
		let empty = hooks.applyFilters('messagebox_is_empty', !content, this, data);
		let valid = hooks.applyFilters('messagebox_validate', !!content, this, data);

		if (empty) {
			this.$btnCancel.hide();
			this.$btnSubmit.hide();
		} else {
			this.$btnCancel.show();
			this.$btnSubmit.show().prop('disabled', !valid);

			currentlyTyping(this.id); // trigger "currently typing"
		}

		// Toggle beforeunload warning if messagebox is not empty.
		if (empty) {
			if (this.beforeUnloadHandler) {
				observer.removeFilter('beforeunload', this.beforeUnloadHandler);
				delete this.beforeUnloadHandler;
			}
		} else {
			if (!this.beforeUnloadHandler) {
				this.beforeUnloadHandler = () => true;
				observer.addFilter('beforeunload', this.beforeUnloadHandler);
			}
		}
	}

	onBtnCancelClick(e) {
		e.preventDefault();
		e.stopPropagation();

		this.cancel().done(() => {});
	}

	onBtnSubmitClick(e) {
		e.preventDefault();
		e.stopPropagation();

		this.submit().done(() => {});
	}

	// Self-contained enter-to-send behavior handler.
	initEnterToSend(flag) {
		let $checkbox = this.$el.find('[data-ps=enter-to-send]');
		if (!$checkbox.length) return;

		$checkbox.removeAttr('disabled').prop('checked', !!flag);
		$checkbox.off('click').on('click', e => {
			ajax.post('messagesajax.enter_to_send', { enter_to_send: +e.target.checked });
		});

		this.$textarea.off('keydown').on('keydown', e => {
			if (!$checkbox.is(':checked')) return;
			if (e.code !== 'Enter') return;

			e.preventDefault();
			e.stopPropagation();

			this.submit().done(() => {});
		});
	}
}

peepso.class('Messagebox', () => Messagebox);
