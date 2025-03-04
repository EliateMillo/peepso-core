import { dialog } from 'peepso';

const TEMPLATE = window.peepsopagesdata && window.peepsopagesdata.dialogCreateTemplate;
const MAX_CATEGORIES = window.peepsopagesdata && +window.peepsopagesdata.max_categories;

(function ($, factory) {
	var PsPageDlgCreate = factory($);

	peepso.pages || (peepso.pages = {});
	peepso.pages.dlgCreate = function (opts) {
		let popup = new PsPageDlgCreate(opts);
		popup.popup.show();
	};
})(jQuery, function ($) {
	/**
	 * Page creation dialog.
	 * @class PsPageDlgCreate
	 */
	function PsPageDlgCreate(opts) {
		this.opts = opts || {};
		this.create();
	}

	peepso.npm.objectAssign(
		PsPageDlgCreate.prototype,
		/** @lends PsPageDlgCreate.prototype */ {
			/**
			 * Dlg template.
			 * @type {string}
			 */
			template: peepsopagesdata.dialogCreateTemplate,

			/**
			 * Initialize dialog.
			 */
			create: function () {
				this.popup = dialog(TEMPLATE, { wide: true }).show();

				this.$el = this.popup.$el;
				this.$submit = this.$el.find('.ps-js-submit');
				this.$loading = this.$el.find('.ps-js-loading');

				this.$el.on('input', '[name=page_name]', $.proxy(this.onInput, this));
				this.$el.on('input', '[name=page_desc]', $.proxy(this.onInput, this));
				this.$el.on('click', '[name=category_id]', $.proxy(this.onSelectCategory, this));
				this.$el.on(
					'click',
					'.ps-js-dropdown--privacy .ps-js-dropdown-menu a',
					$.proxy(this.onChangePrivacy, this)
				);
				this.$el.on('click', '.ps-js-cancel', $.proxy(this.onClose, this));
				this.$el.on('click', '.ps-js-submit', $.proxy(this.onSubmit, this));
				this.$el.appendTo(document.body);
			},

			onInput: function (e) {
				var $input = $(e.currentTarget),
					$limit = $input.parent().find('.ps-js-limit'),
					maxLength = +$input.data('maxlength'),
					val = $input.val();

				if (maxLength && val.length > maxLength) {
					val = val.substr(0, maxLength);
					$input.val(val);
				}

				$limit.html(maxLength - val.length);
			},

			onSelectCategory: function (e) {
				if ('radio' === e.target.type) {
					return;
				}

				var $categories = this.$el.find('[name=category_id]'),
					$checked = $categories.filter(':checked'),
					$unchecked = $categories.not($checked);

				if ($checked.length >= MAX_CATEGORIES) {
					$unchecked.prop('disabled', true);
					$unchecked.parent().css('opacity', 0.3);
				} else {
					$unchecked.prop('disabled', false);
					$unchecked.parent().css('opacity', '');
				}
			},

			onChangePrivacy: function (e) {
				var $a = $(e.currentTarget),
					$dropdown = $a.closest('.ps-js-dropdown'),
					$toggle = $dropdown.find('.ps-js-dropdown-toggle');

				// Update label.
				$toggle
					.find('.dropdown-value span')
					.text($a.find('.ps-dropdown__page-title span').text());

				// Update icon.
				$toggle
					.find('.dropdown-value [class*=ps-icon]')
					.attr(
						'class',
						$a.find('.ps-dropdown__page-title [class*=ps-icon]').attr('class')
					);

				// Update hidden input.
				this.$el.find('input[name=page_privacy]').val($a.data('optionValue'));
			},

			onClose: function (e) {
				e.preventDefault();
				e.stopPropagation();
				this.$el.remove();
			},

			onSubmit: function () {
				var name = $.trim(this.$el.find('[name=page_name]').val()),
					description = $.trim(this.$el.find('[name=page_desc]').val()),
					category_id = new Array(),
					privacy = this.$el.find('[name=page_privacy]').val(),
					nonce = this.$el.find('[name=_wpnonce]').val(),
					req;

				this.$el.find('[name=category_id]:checked').each(function () {
					category_id.push($(this).val());
				});

				req = {
					name: name,
					description: description,
					category_id: category_id,
					meta: { privacy: privacy },
					_wpnonce: nonce
				};

				// Custom input fields.
				this.$el.find('input, textarea, select').filter('.ps-js-custom-input').each(function () {
					var $input = $(this),
						name = $input.attr('name');

					if (name) {
						if ($input.is(':checkbox')) {
							if ($input.is(':checked')) {
								req[`${name}[]`] = req[`${name}[]`] || [];
								req[`${name}[]`].push($input.val());
							}
						} else if ($input.is(':radio')) {
							if ($input.is(':checked')) {
								req[name] = $input.val();
							}
						} else {
							req[name] = $input.val();
						}
					}
				});

				this.$submit.attr('disabled', 'disabled');
				this.$loading.show();

				peepso.postJson(
					'pageajax.create',
					req,
					function (json) {
						this.$loading.hide();
						this.$submit.removeAttr('disabled');

						if (json.success) {
							if (json.data && json.data.redirect) {
								window.location = json.data.redirect;
							}
						} else if (json.errors && json.errors.length) {
							var error = json.errors[0] || {};

							if (error.name) {
								this.$el.find('.ps-js-error-name').html(error.name).show();
							} else {
								this.$el.find('.ps-js-error-name').hide();
							}
							if (error.description) {
								this.$el.find('.ps-js-error-desc').html(error.description).show();
							} else {
								this.$el.find('.ps-js-error-desc').hide();
							}
							if (error.category_id) {
								this.$el
									.find('.ps-js-error-category_id')
									.html(error.category_id)
									.show();
							} else {
								this.$el.find('.ps-js-error-category_id').hide();
							}
						}
					}.bind(this)
				);
			}
		}
	);

	return PsPageDlgCreate;
});
