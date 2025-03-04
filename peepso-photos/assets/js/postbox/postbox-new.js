peepso.class('PostboxTypePhoto', function (name, peepso, $) {
	const { hooks } = peepso;

	hooks.addAction('postbox_init', 'photo', postbox => new (peepso.class(name))(postbox));

	return class extends peepso.class('PostboxType') {
		constructor(postbox, type = 'photo') {
			super(postbox, type);

			this.valid = false;
			this.dropzone = null;
			this.lastData = null;

			this.$uploadButton = this.$postbox.find('.ps-postbox-photo-upload');
			this.$uploadPreview = this.$postbox.find('.ps-postbox-preview');

			this.$uploadButton.on('click', () => this.upload());
		}

		show() {
			super.show();
			this.dropzoneInit();
		}

		upload() {
			this.dropzone.triggerUpload();
		}

		onFilterData(data, postbox) {
			if (postbox === this.postbox && this.active) {
				// Detect change post_to option.
				this.maybeMoveTempFiles(data, this.lastData);
				this.lastData = Object.assign({}, data);

				data.type = 'photo';
				data.files = this.dropzone.getPhotos();
			}

			return data;
		}

		onFilterValidate(valid, postbox, data) {
			if (postbox === this.postbox && this.active) {
				valid = this.valid;
			}

			return valid;
		}

		onFilterIsEmpty(empty, postbox, data) {
			if (postbox === this.postbox && this.active) {
				if (data.files.length > 0) empty = false;
			}

			return empty;
		}

		onActionSaved(postbox) {
			if (postbox === this.postbox) {
				this.saved = true;
			}
		}

		onActionReset(postbox) {
			if (postbox === this.postbox) {
				this.hide();
				this.dropzone && this.dropzone.empty(this.saved ? 'save' : '');
				this.lastData = null;
				this.$uploadPreview.hide();
				this.$uploadButton.show();

				delete this.saved;
			}
		}

		onClickToggle(e) {
			super.onClickToggle(e);

			if (!this.dropzone.getPhotos().length) {
				this.upload();
			}
		}

		onClickShortcut(e) {
			super.onClickShortcut(e);

			if (!this.dropzone.getPhotos().length) {
				this.upload();
			}
		}

		dropzoneInit() {
			if (!this.dropzone) {
				this.dropzone = new peepso.PhotoDropzone(this.$uploadPreview);
				this.dropzone.on('photo_added', () => this.dropzonePhotoAdded());
				this.dropzone.on('photo_upload_start', () => this.dropzonePhotoUploadStart());
				this.dropzone.on('photo_upload_done', num => this.dropzonePhotoUploadDone(num));
				this.dropzone.on('photo_empty', () => this.dropzonePhotoEmpty());

				// Add the upload button as a droppable element.
				this.dropzone.addDroppable(this.$uploadButton[0]);
			}
		}

		dropzonePhotoAdded() {
			this.$uploadButton.hide();
			this.$uploadPreview.show();
		}

		dropzonePhotoUploadStart() {
			this.valid = false;
		}

		dropzonePhotoUploadDone(remaining) {
			if (remaining <= 0) {
				this.valid = true;
				this.postbox.$textarea.trigger('input');
			}
		}

		dropzonePhotoEmpty() {
			this.valid = false;
			this.$uploadPreview.hide();
			this.$uploadButton.show();
			this.postbox.$textarea.trigger('input');
		}

		maybeMoveTempFiles(data, lastData) {
			if (!lastData) return;
			if (this.saved) return;

			let photos = this.dropzone.getPhotos();
			if (!(photos && photos.length)) return;

			if (data.page_id !== lastData.page_id || data.group_id !== lastData.group_id) {
				let params = {
					user_id: peepsodata.currentuserid,
					old_group_id: lastData.group_id,
					old_page_id: lastData.page_id,
					group_id: data.group_id,
					page_id: data.page_id,
					photo: photos,
					// Retrieve from inside the dropzone element.
					_wpnonce: this.$typeInput.find('[name=_wpnonce_remove_temp_files]').val(),
					_wp_http_referer: this.$typeInput.find('[name=_wp_http_referer]').val()
				};

				peepso.postJson('photosajax.move_temp_files', params);
			}
		}
	};
});
