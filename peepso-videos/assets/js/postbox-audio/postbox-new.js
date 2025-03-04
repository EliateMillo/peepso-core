import _ from 'underscore';
import peepso, { browser, dialog, observer } from 'peepso';
import Audio from '../audio';

peepso.class('PostboxTypeAudio', function (name, peepso, $) {
	const { hooks } = peepso;

	hooks.addAction('postbox_init', 'audio', postbox => new (peepso.class(name))(postbox));

	return class extends peepso.class('PostboxType') {
		constructor(postbox, type = 'audio') {
			super(postbox, type);

			this.config = window.peepsoaudiosdata || {};

			// Embed.
			this.$embed = this.$typeInput.find('.ps-js-audio-embed');
			this.$embedUrl = this.$embed.find('.ps-js-url');
			this.$embedLoading = this.$embed.find('.ps-js-loading');
			this.$embedUrl.on(
				'input',
				_.debounce(e => this.embed(e.target.value), 2000)
			);
			this.$embedUrl.on('paste', e => _.defer(() => this.embed(e.target.value)));
			this.$embedUrl.on('blur', e => {
				this.$uploadBtn.off('click');
				_.defer(() => this.$uploadBtn.on('click', () => this.onUploadClick()));
				this.embed(e.target.value);
			});

			// Uploader.
			this.$upload = this.$typeInput.find('.ps-js-audio-upload');
			this.$uploadBtn = this.$upload.find('.ps-js-btn');
			this.$uploadForm = this.$upload.find('.ps-js-form');
			this.$uploadTitle = this.$upload.find('.ps-js-title');
			this.$uploadArtist = this.$upload.find('.ps-js-artist');
			this.$uploadAlbum = this.$upload.find('.ps-js-album');
			this.$uploadProgress = this.$upload.find('.ps-js-progress');
			this.$uploadDone = this.$upload.find('.ps-js-done');
			this.$uploadFailed = this.$upload.find('.ps-js-failed');
			this.$uploadSuccess = this.$upload.find('.ps-js-success');
			this.$uploadBtn.on('click', () => this.onUploadClick());

			// Separator.
			this.$separator = this.$postbox.find('.ps-js-audio-separator');
			this.$preview = this.$postbox.find('.ps-js-audio-preview');

			this.mode = null;
			this.data = null;

			this.uploadInit();
		}

		onFilterData(data, postbox) {
			if (postbox === this.postbox && this.active) {
				data.type = 'audio';

				if ('embed' === this.mode) {
					data.url = this.data.url;
				} else if ('upload' === this.mode) {
					data.audio = this.data.file;
					data.audio_title = this.$uploadTitle.val().trim();
					data.audio_artist = this.$uploadArtist.val().trim();
					data.audio_album = this.$uploadAlbum.val().trim();
				}
			}

			return data;
		}

		onFilterValidate(valid, postbox, data) {
			if (postbox === this.postbox && this.active) {
				if ('embed' === this.mode) {
					valid = !!data.url;
				} else if ('upload' === this.mode) {
					valid = !!data.audio;
				} else {
					valid = false;
				}
			}

			return valid;
		}

		onFilterIsEmpty(empty, postbox, data) {
			if (postbox === this.postbox && this.active) {
				if ('embed' === this.mode && !!data.url) empty = false;
				else if ('upload' === this.mode && !!data.audio) empty = false;
			}

			return empty;
		}

		onActionReset(postbox) {
			if (postbox === this.postbox) {
				this.hide();

				this.mode = null;
				this.data = null;
				this.$embedUrl.val((this._embedUrl = ''));
				this.$preview.empty();
				this.postbox.$textarea.trigger('click');
			}
		}

		show() {
			super.show();

			this.mode = null;
			this.$embed.show();
			this.$separator.show();
			this.$upload.show();
			this.$uploadBtn.show();
			this.$uploadForm.hide();
			this.$uploadSuccess.hide();
			this.$preview.hide();

			if (+this.config.upload_enable) {
				// this.$postboxStatus.show();
			} else {
				// this.$postboxStatus.show();
				this.mode = 'embed';
			}
		}

		/**
		 * Switch UI to show audio embed form.
		 */
		showEmbed() {
			this.mode = 'embed';
			this.$embed.show();
			this.$separator.hide();
			this.$upload.hide();
			this.$preview.show();
		}

		/**
		 * Swith UI to show audio upload form.
		 */
		showUpload() {
			this.mode = 'upload';
			// this.$postboxStatus.show();
			this.$embed.hide();
			this.$separator.hide();
			this.$upload.show();
			this.$uploadTitle.val('');
			this.$uploadBtn.hide();
			this.$uploadForm.show();
			this.$uploadProgress.show();
			this.$uploadDone.hide();
			this.$uploadFailed.hide();
			this.$preview.show();
		}

		/**
		 * Cancel creating audio post.
		 */
		cancel() {
			this.mode = null;
			this.data = null;
			this.$embedUrl.val((this._embedUrl = ''));
			this.$preview.empty();
			this.$postbox.on_change();
		}

		/**
		 * Finalize creating audio post.
		 */
		post() {
			let filterName = 'postbox_req_' + this.$postbox.guid;

			observer.addFilter(filterName, this.postSetRequest, 10, 1, this);
			this.$postbox.save_post();
			observer.removeFilter(filterName, this.postSetRequest, 10);
		}

		postSetRequest(req) {
			if ('embed' === this.mode) {
				_.extend(req, {
					type: 'audio',
					url: this.data.url
				});
			} else if ('upload' === this.mode) {
				_.extend(req, {
					type: 'audio',
					audio: this.data.file,
					audio_title: this.$uploadTitle.val().trim()
				});
			}

			return req;
		}

		/**
		 * Handle embed url.
		 *
		 * @param {string} url
		 */
		embed(url = '') {
			url = url.trim();

			// Skip if url is not valid.
			if (!url.match(/^https?:\/\//i)) {
				return;
			}

			// Skip if url is not changed.
			if (url === this._embedUrl) {
				return;
			}
			this._embedUrl = url;

			// Abort previous audio fetching.
			if (this.audio) {
				try {
					this.audio.fetchAbort();
				} catch (e) {}
			}

			// Fetch embed audio information.
			this.showEmbed();
			this.$embedLoading.show();
			this.audio = new Audio(url);
			this.audio
				.getHTML()
				.then(html => {
					this.$preview.html(html);
					this.audio.getData().then(data => {
						this.data = data;
						this.postbox.$textarea.trigger('input');
					});
				})
				.catch(error => {
					this.$embedUrl.val((this._embedUrl = ''));
					this.show();
					dialog(error, { error: true }).show();
				})
				.finally(() => {
					this.$embedLoading.hide();
				});
		}

		onUploadClick(e) {
			if (!this.uploadInitialized) {
				this.uploadInit();
				this.uploadInitialized = true;
			}

			// Try to clear input value first if possible, to make sure
			// onchange event is triggered even if the same file is selected.
			try {
				this.$uploadFile[0].value = null;
			} catch (ex) {}

			this.$uploadFile.click();
		}

		uploadInit() {
			if (!this.$uploadFile) {
				this.$uploadFile = this.$upload.find('.ps-js-file');
				this.$uploadFile.psFileupload({
					replaceFileInput: false,
					dropZone: this.$uploadBtn,
					add: (e, data) => {
						let file = data,
							$progressbar = this.$uploadProgress.find('.ps-js-percent-bar'),
							$percent = this.$uploadProgress.find('.ps-js-percent');

						this.showUpload();
						$percent.html('');
						$progressbar.css({ transition: '', width: 1 });

						this.audio = new Audio(file);
						this.audio.on(
							'progress',
							_.throttle(
								percent => {
									let label = +percent,
										width = Math.min(100, +percent);

									// Consistently use 2 fractional digits.
									if (label > 0 && label < 100) {
										label = label.toFixed(2);
									}

									$percent.html(`${label}%`);
									$progressbar.css({
										transition: 'width 1s',
										width: `${width}%`
									});

									if (width === 100) {
										setTimeout(() => {
											this.$uploadProgress.hide();
										}, 1500);
									}
								},
								browser.isIOS() ? 1500 : 250
							)
						);

						this.audio
							.getHTML()
							.then(() => {
								this.$uploadDone.show();
								this.audio.getData().then(data => {
									this.data = data;
									this.postbox.$textarea.trigger('input');
								});
							})
							.catch(error => {
								this.show();

								if (!error) {
									error = 'Undefined error.';
								} else if (error.errors) {
									error = error.errors;
								}

								dialog(error).error();
							});
					}
				});
			}
		}
	};
});
