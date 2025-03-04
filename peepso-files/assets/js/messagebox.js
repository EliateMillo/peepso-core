import { rest_url as REST_URL, file as fileData } from 'peepsodata';
import Dropzone from './dropzone';

function maybeDeclare(name) {
	if (peepso.class(name)) return;

	peepso.class(name, function (name, peepso, $) {
		return class extends peepso.class('MessageboxType') {
			constructor(messagebox, type = 'file') {
				super(messagebox, type);

				this.valid = false;
				this.dropzone = null;

				this.$uploadButton = this.$el.find('.ps-js-file-upload').show();
				this.$uploadPreview = this.$el.find('.ps-js-file-preview').hide();

				this.$uploadButton.on('click', () => this.upload());

				this.dropzone = new Dropzone(this.$uploadPreview.find('.ps-js-file-container'), {
					uploadUrl: `${REST_URL}${fileData.uploadUrl}`,
					uploadFileTypes: fileData.uploadFileTypes
				});

				this.dropzone.on('added', () => {
					this.valid = false;
					this.showPreview();
				});

				this.dropzone.on('uploaded', () => {
					this.valid = true;
					this.messagebox.$textarea.trigger('input');
				});

				this.dropzone.on('empty', () => {
					this.valid = false;
					this.showButton();
					this.messagebox.$textarea.trigger('input');
				});
			}

			showButton() {
				this.$uploadPreview.hide();
				this.$uploadButton.show();
			}

			showPreview() {
				this.$uploadButton.hide();
				this.$uploadPreview.show();
			}

			upload() {
				this.dropzone.upload();
			}

			onFilterData(data, messagebox) {
				if (messagebox === this.messagebox && this.active) {
					data.type = 'files';
					data.files = this.dropzone.getFiles().map(file => file.name);
				}

				return data;
			}

			onFilterValidate(valid, messagebox, data) {
				if (messagebox === this.messagebox && this.active) {
					valid = data.files && data.files.length;
				}

				return valid;
			}

			onFilterIsEmpty(empty, messagebox, data) {
				if (messagebox === this.messagebox && this.active) {
					if (data.files.length > 0) empty = false;
				}

				return empty;
			}

			onActionReset(messagebox) {
				if (messagebox === this.messagebox) {
					this.hide();
					this.dropzone.reset();
					this.showButton();
					this.messagebox.$textarea.trigger('input');
				}
			}

			onClickToggle(e) {
				super.onClickToggle(e);

				if (!this.dropzone.getFiles().length) {
					this.upload();
				}
			}

			onClickShortcut(e) {
				super.onClickShortcut(e);

				if (!this.dropzone.getFiles().length) {
					this.upload();
				}
			}
		};
	});
}

peepso.hooks.addAction('messagebox_init', 'file', messagebox => {
	const name = 'MessageboxTypeFile';
	maybeDeclare(name);
	new (peepso.class(name))(messagebox);
});
