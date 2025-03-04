var PageActions = require('./page-actions').default;

(function ($, peepso, factory) {
	/**
	 * PsPages global instance.
	 * @name ps_pages
	 * @type {PsPages}
	 */
	window.ps_pages = new (factory($, peepso))();
})(jQuery, peepso, function ($, peepso) {
	/**
	 * PsPages class.
	 * @class PsPages
	 */
	function PsPages() {
		peepso.observer.addFilter(
			'page_update_member_count',
			function (page_id, member_count) {
				var $item = $('.ps-js-page-item--' + page_id),
					$count = $item.find('.ps-js-member-count'),
					label,
					html;
				if ($count.length) {
					label =
						+member_count > 1
							? peepsopagesdata.lang.members
							: peepsopagesdata.lang.member;
					$count.html(member_count + ' ' + label);
				}
			},
			10,
			2
		);

		$(document.body).on(
			'click',
			'.ps-js-pages .ps-js-member-action',
			$.proxy(function (e) {
				var $btn = $(e.currentTarget),
					$loading,
					data,
					method,
					confirm;

				e.preventDefault();
				e.stopPropagation();

				if ($btn.data('ps-loading')) {
					return;
				}

				data = $.extend({}, $btn.data());
				if (!data.method || !data.id) {
					return;
				}

				$loading = $btn.find('img');
				if (!$loading.length && $btn.parent().hasClass('ps-js-dropdown-menu')) {
					$loading = $btn.parent().siblings('.ps-js-dropdown-toggle');
					$loading = $loading.find('img');

					// Hide dropdown automatically if loading is on the trigger button.
					$btn.parent().hide();
				}

				method = data.method;
				confirm = data.confirm;
				data.page_id = data.id;
				delete data.method;
				delete data.confirm;
				delete data.id;

				this._member_action_confirmation(confirm).done(function () {
					$btn.data('ps-loading', true);
					$loading.show();

					this.member_action(method, data).done(function (json) {
						var $actions = $btn.closest('.ps-js-member-actions'),
							actionsTemplate = peepso.template(
								peepsopagesdata.listItemMemberActionsTemplate
							),
							html = actionsTemplate($.extend({ id: data.page_id }, json));

						$actions.html(html);
						if (json.member_count) {
							peepso.observer.applyFilters(
								'page_update_member_count',
								data.page_id,
								json.member_count
							);
						}
					});
				});
			}, this)
		);

		$(document.body).on(
			'click',
			'.ps-js-pages .ps-js-more',
			$.proxy(function (e) {
				var itemSelector = '.ps-js-page-item',
					expandedClassName = 'ps-page--expanded',
					$wrapper = $(e.currentTarget).closest(itemSelector);

				e.preventDefault();
				e.stopPropagation();

				$('.ps-pages')
					.find(itemSelector)
					.each(function () {
						var $item = $(this);
						if ($item.is($wrapper) && !$item.hasClass(expandedClassName)) {
							$item.addClass(expandedClassName);
							$item.find('.ps-js-more span').html(peepsopagesdata.lang.less);
						} else if ($item.hasClass(expandedClassName)) {
							$item.removeClass(expandedClassName);
							$item.find('.ps-js-more span').html(peepsopagesdata.lang.more);
						}
					});
			}, this)
		);
	}

	/**
	 * @memberof PsPages
	 * @param {object} params
	 */
	PsPages.prototype._fetch = function (params) {
		return $.Deferred(
			$.proxy(function (defer) {
				var category = (params.category || 0) + '';
				this._fetch_xhr = this._fetch_xhr || {};
				this._fetch_xhr[category] && this._fetch_xhr[category].abort();
				this._fetch_xhr[category] = peepso.disableAuth().getJson(
					'pagesajax.search',
					params,
					$.proxy(function (response) {
						var itemTemplate = peepso.template(peepsopagesdata.listItemTemplate),
							actionsTemplate = peepso.template(
								peepsopagesdata.listItemMemberActionsTemplate
							),
							data = response.data || {},
							html = '',
							pageData,
							reQuery,
							highlight,
							actions,
							isMarkdown,
							i;

						if (data.pages && data.pages.length) {
							// page listing found
							for (i = 0; i < data.pages.length; i++) {
								pageData = data.pages[i];
								pageData.nameHighlight = pageData.name || '';

								// Parse markdown content.
								isMarkdown = pageData.description.match('peepso-markdown');
								if (isMarkdown) {
									// Parse markdown content.
									pageData.description = peepso.observer.applyFilters(
										'peepso_parse_content',
										'<div class="peepso-markdown">' +
											pageData.description +
											'</div>'
									);
								} else {
									// Decode html entities on description.
									pageData.description = $('<div/>')
										.html(pageData.description || '')
										.text();
								}

								if (params.query) {
									reQuery = params.query.replace(
										/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g,
										'\\$&'
									);
									reQuery = RegExp('(' + reQuery + ')', 'ig');
									highlight =
										'<span style="background:' +
										peepso.getLinkColor(0.3) +
										'">$1</span>';
									pageData.nameHighlight = pageData.nameHighlight.replace(
										reQuery,
										highlight
									);

									if (!isMarkdown) {
										// Only highlight keyword if it is not markdown.
										pageData.description = (
											pageData.description || ''
										).replace(reQuery, highlight);
									}
								}

								html += itemTemplate(
									$.extend({}, pageData, { member_actions: '' })
								);

								// Render page actions.
								_.defer(function (data) {
									var $card = $('.ps-js-page-item--' + data.id),
										$actions = $card.find('.ps-js-member-actions'),
										actions;

									actions = new PageActions({
										id: data.id,
										member_actions: data.pageuserajax.member_actions,
										follower_actions: data.pagefollowerajax.follower_actions
									});

									$actions.html(actions.$el);
								}, pageData);
							}
							defer.resolveWith(this, [html]);
						} else if (params.page > 1) {
							// empty list
							defer.resolveWith(this, [null]);
						} else {
							// error
							defer.rejectWith(this, [response.errors]);
						}
					}, this)
				);
			}, this)
		);
	};

	/**
	 * TODO: should be part of `PsPage` class
	 * @memberof PsPages
	 * @param {string} method
	 * @param {object} data
	 */
	PsPages.prototype._member_action_confirmation = function (confirm) {
		return $.Deferred(
			$.proxy(function (defer) {
				if (confirm) {
					pswindow.confirm(
						confirm,
						$.proxy(function () {
							pswindow.hide();
							defer.resolveWith(this);
						}, this),
						$.proxy(function () {
							defer.rejectWith(this);
						}, this)
					);
				} else {
					defer.resolveWith(this);
				}
			}, this)
		);
	};

	/**
	 * TODO: should be part of `PsPage` class
	 * @memberof PsPages
	 * @param {string} method
	 * @param {object} data
	 */
	PsPages.prototype.member_action = function (method, data) {
		return $.Deferred(
			$.proxy(function (defer) {
				if ((method || '').indexOf('.') < 0) {
					method = 'pageuserajax.' + method;
				}
				peepso.postJson(
					method,
					data,
					$.proxy(function (response) {
						if (response.success) {
							defer.resolveWith(this, [response.data]);
						} else {
							defer.rejectWith(this, [response.errors]);
						}
					}, this)
				);
			}, this)
		);
	};

	return PsPages;
});
