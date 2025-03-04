import $ from 'jquery';
import { ajax, observer } from 'peepso';

const messagesData = window.peepsomessagesdata || {};
const FRIENDS_ONLY = +messagesData.friends_only ? 'is_friend' : false;

class MessageNew {
	constructor(opts = {}) {
		this.$container = $(opts.el);
		this.$postbox = this.$container.find('.ps-postbox-message');
		this.$recipientsSingle = this.$container.find('.ps-js-recipient-single').hide();
		this.$recipientsMultiple = this.$container.find('.ps-js-recipient-multiple').hide();
		this.$recipientsSelect = this.$container.find('select[name=recipients]');

		opts.args = opts.args || {};
		let { id } = opts.args;
		this.user_id = id;
		this.flag = FRIENDS_ONLY ? 'is_friend' : null;
		this.args = opts.args;
		delete opts.args.id;

		this.initPostbox();
		this.initRecipientsForm();
	}

	initPostbox() {
		this.$postbox = this.$postbox.pspostbox({
			autosize: true,
			text_length: messagesData.character_limit,
			save_url: 'messagesajax.new_message',
			send_button_text: messagesData.send_button_text,
			postbox_req: params => {
				let recipients = this.$recipientsSelect.val();
				let data = { recipients, subject: '', message: params.content };
				data = Object.assign(data, this.args);
				console.log(data);
				return data;
			},
			on_save: json => {
				if (json.data && json.data.url) {
					observer.removeFilter('beforeunload', this.$postbox.beforeUnloadHandler);
					window.location = json.data.url;
				}
			},
			on_error: json => json.errors && alert(json.errors[0])
		});
	}

	async initRecipientsForm() {
		let recipients = await this.getAvailableRecipients(this.user_id, this.flag);

		// Users' avatar lookup table.
		let avatars = {};
		recipients.forEach(user => (avatars[user.id] = user.avatar));

		// Single recipient.
		if (this.user_id) {
			let recipient = recipients.find(u => u.id == this.user_id);
			if (recipient) {
				let optionHtml = `<option value="${recipient.id}">${recipient.display_name}</option>`;
				this.$recipientsSelect.html(optionHtml).val(recipient.id);

				let $rec = this.$recipientsSingle;
				$rec.find('img').attr('src', recipient.avatar).attr('alt', recipient.display_name);
				$rec.find('.ps-comment-user').html(recipient.display_name);
				$rec.find('a').attr('href', recipient.url);
				$rec.show();
			}
			return;
		}

		// Multiple
		this.$recipientsMultiple.show();
		this.$recipientsSelect.selectize({
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
			load: (keyword, callback) => {
				this.getAvailableRecipients(this.user_id, this.flag, keyword).then(callback);
			},
			onInitialize() {
				this.$wrapper.append(`<img src="${this.$input.data('loading')}" />`);
				this.$control_input.on('input', e => {
					setTimeout(() => $(e.currentTarget).trigger('keyup'), 0);
				});
			}
		});

		if (this.user_id) {
			this.$recipientsMultiple.hide();
			this.$recipientsSingle.show();

			let selectize = this.$recipientsSelect[0].selectize;
			selectize.onSearchChange('');
		} else {
			this.$recipientsSingle.hide();
			this.$recipientsMultiple.show();
		}
	}

	async getAvailableRecipients(user_id, flag, keyword) {
		let endpoint = 'messagesajax.get_available_recipients';
		let params = { user_id, keyword };
		let json = await ajax.post(endpoint, params);
		let recipients = [];

		if (json.success) {
			recipients = json.data.available_participants || [];
			if (user_id) recipients = recipients.filter(u => u.id == user_id);
			if (flag) recipients = recipients.filter(u => u[flag]);
		}

		return recipients;
	}
}

export default MessageNew;
