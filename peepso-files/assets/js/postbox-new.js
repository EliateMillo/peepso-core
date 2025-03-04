import { rest_url as REST_URL, file as fileData } from 'peepsodata';
import Dropzone from './dropzone';

peepso.class('PostboxTypeFile', function (name, peepso, $) {
	const { hooks } = peepso;

	hooks.addAction('postbox_init', 'file', postbox => new (peepso.class(name))(postbox));

	return class extends peepso.class('PostboxType') {
		constructor(postbox, type = 'file') {
			super(postbox, type);

			this.valid = false;
			this.dropzone = null;

			this.$uploadButton = this.$postbox.find('.ps-js-file-upload').show();
			this.$uploadPreview = this.$postbox.find('.ps-js-file-preview').hide();

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
				this.postbox.$textarea.trigger('input');
			});

			this.dropzone.on('empty', () => {
				this.valid = false;
				this.showButton();
				this.postbox.$textarea.trigger('input');
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

		onFilterData(data, postbox) {
			if (postbox === this.postbox && this.active) {
				data.type = 'files';
				data.files = this.dropzone.getFiles().map(file => file.name);
			}

			return data;
		}

		onFilterValidate(valid, postbox, data) {
			if (postbox === this.postbox && this.active) {
				valid = data.files && data.files.length;
			}

			return valid;
		}

		onFilterIsEmpty(empty, postbox, data) {
			if (postbox === this.postbox && this.active) {
				if (data.files.length > 0) empty = false;
			}

			return empty;
		}

		onActionReset(postbox) {
			if (postbox === this.postbox) {
				this.hide();
				this.dropzone.reset();
				this.showButton();
				this.postbox.$textarea.trigger('input');
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
