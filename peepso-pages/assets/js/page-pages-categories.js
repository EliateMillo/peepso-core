import { ls } from 'peepso';

(function ($, factory) {
	var PsPagePagesCategories = factory($);
	var ps_page_pages_categories = new PsPagePagesCategories('.ps-js-page-cats');
})(jQuery, function ($) {
	var CONFIG = (peepsodata.pages && peepsodata.pages.categories) || {};
	var CONFIG_EXPAND_ALL = +CONFIG.pages_categories_expand_all;
	var CONFIG_GROUP_COUNT = +CONFIG.pages_categories_page_count || 4;

	function PsPagePagesCategories() {
		if (PsPagePagesCategories.super_.apply(this, arguments)) {
			$($.proxy(this.init_page, this));
		}
	}

	// inherit from `PsPageAutoload`
	peepso.npm.inherits(PsPagePagesCategories, PsPageAutoload);

	peepso.npm.objectAssign(PsPagePagesCategories.prototype, {
		init_page: function () {
			if (!this._search_$ct.length) {
				return;
			}

			this._search_$ct.on(
				'click',
				'.ps-js-pages-cat .ps-js-page-category-title',
				$.proxy(this._toggle, this)
			);

			// Toggle view mode on the page listing.
			var mode = this._search_$ct.data('mode') || 'grid';
			if (+peepsodata.currentuserid) {
				var userMode = ls.get('pages_viewmode_' + peepsodata.currentuserid);
				mode = userMode || mode;
			}

			this.toggleViewMode(mode);
			$('.ps-js-pages-viewmode').on('click', e => {
				e.preventDefault();

				let mode = $(e.currentTarget).data('mode');

				if (+peepsodata.currentuserid) {
					ls.set('pages_viewmode_' + peepsodata.currentuserid, mode);
				}

				this.toggleViewMode(mode);
			});
		},

		_search_url: 'pagecategoriesajax.search',

		_search_params: {
			keys: 'id,name,description,url,pages_count',
			page: 1
		},

		_search_debounced: _.debounce(function () {
			this._fetch(this._search_params)
				.done(function (data) {
					var html = this._search_render_html(data && data.page_categories);
					this._search_toggle_loading('hide');
					if (html) {
						this._search_$ct.append(html);
						this._search_toggle_autoscroll('on');
					} else {
						this._render_uncategorized();
						this._search_$nomore.show();
					}
				})
				.fail(function (errors) {
					this._search_toggle_loading('hide');
					if (this._search_params.page <= 1) {
						this._render_uncategorized()
							.done(
								$.proxy(function () {
									this._search_$nomore.show();
								}, this)
							)
							.fail(
								$.proxy(function () {
									this._search_$ct.html(errors.join('<br>'));
								}, this)
							);
					} else {
						this._render_uncategorized();
						this._search_$nomore.show();
					}
				});
		}, 500),

		/**
		 * Toggle view mode.
		 *
		 * @param {string} mode
		 */
		toggleViewMode: function (mode) {
			let $buttons = $('.ps-js-pages-viewmode'),
				$active = $buttons.filter(`[data-mode="${mode}"]`),
				$lists = $('.ps-js-pages'),
				activeClass = 'ps-btn--active',
				listSingleClass = 'ps-pages__list--single';

			$active.addClass(activeClass);
			$buttons.not($active).removeClass(activeClass);

			if ('list' === mode) {
				$lists.addClass(listSingleClass);
			} else {
				$lists.removeClass(listSingleClass);
			}
		},

		_search_render_html: function (categories) {
			var template = peepso.template(peepsopagesdata.listCategoriesTemplate),
				html = null,
				firstCategory;

			if (categories && categories.length) {
				html = '';
				_.each(categories, function (item) {
					html += template(_.extend({}, item));
					if (!firstCategory) {
						firstCategory = item;
					}
				});
			}

			// Auto-unfold all categories if config is enabled.
			if (CONFIG_EXPAND_ALL) {
				setTimeout(
					$.proxy(function () {
						var $collapsed = this._search_$ct
							.find('.ps-js-pages-cat')
							.filter(function () {
								return $(this)
									.find('.ps-js-page-category-action i')
									.hasClass('gci-expand-alt');
							});
						$collapsed.find('.ps-js-page-category-title').trigger('click');
					}, this),
					1
				);
			}

			// Auto-unfold first category if there are pages with that category.
			else if (firstCategory && +firstCategory.pages_count > 0) {
				setTimeout(
					$.proxy(function () {
						if (!this._unfold_first_row) {
							this._unfold_first_row = true;
							this._search_$ct
								.find('.ps-js-pages-cat')
								.eq(0)
								.find('.ps-js-page-category-title')
								.trigger('click');
						}
					}, this),
					1
				);
			}

			// Re-toggle view mode after HTML is inserted to the document.
			setTimeout(() => {
				var mode = this._search_$ct.data('mode') || 'grid';
				if (+peepsodata.currentuserid) {
					var userMode = ls.get('pages_viewmode_' + peepsodata.currentuserid);
					mode = userMode || mode;
				}

				this.toggleViewMode(mode);
			}, 1);

			return html;
		},

		_search_get_items: function () {
			return this._search_$ct.children('.ps-js-page-category-item');
		},

		/**
		 * @param {object} params
		 * @returns jQuery.Deferred
		 */
		_fetch: function (params) {
			return $.Deferred(
				$.proxy(function (defer) {
					// Multiply limit value by 2 which translate to 2 rows each call.
					params = $.extend({}, params);
					if (!_.isUndefined(params.limit)) {
						params.limit *= 2;
					}

					this._fetch_xhr && this._fetch_xhr.abort();
					this._fetch_xhr = peepso
						.disableAuth()
						.disableError()
						.getJson(
							this._search_url,
							params,
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
		},

		_toggle: function (e) {
			// Skip toggle when category title is clicked.
			if ('A' === e.target.tagName) {
				if (e.target.href.match(/^https?:\/\//)) {
					return;
				}
			}

			e.preventDefault();
			e.stopPropagation();
			this._toggle_throttled(e);
		},

		_toggle_throttled: _.throttle(function (e) {
			var $icon = $(e.currentTarget).find('.ps-js-page-category-action i'),
				$elem = $icon.closest('.ps-js-pages-cat'),
				expandedIcon = 'gcis gci-compress-alt',
				collapsedIcon = 'gcis gci-expand-alt',
				isExpanded = $icon.hasClass(expandedIcon);

			if (isExpanded) {
				$icon.attr('class', collapsedIcon);
				$elem.find('.ps-js-pages').hide();
				this._collapse($elem);
			} else {
				$icon.attr('class', expandedIcon);
				$elem.find('.ps-js-pages').show();
				this._expand($elem);
			}
		}, 500),

		_expand: function ($elem) {
			var loadedClass = 'ps-js-loaded',
				params;

			$elem.addClass('ps-pages__category--open');

			// exit if items already loaded
			if ($elem.hasClass(loadedClass)) {
				return;
			}

			params = {
				uid: peepsodata.currentuserid,
				user_id: undefined,
				query: '',
				category: $elem.data('id'),
				order_by: undefined,
				order: undefined,
				admin: undefined,
				keys:
					'id,name,description,date_created_formatted,members_count,url,published,avatar_url_full,cover_url,privacy,pageuserajax.member_actions,pagefollowerajax.follower_actions',
				limit: CONFIG_GROUP_COUNT,
				page: 1
			};

			// execute fetch function on `PsPages` class
			ps_pages
				._fetch(params)
				.done(function (html) {
					$elem.addClass(loadedClass);
					$elem.find('.ps-js-pages').html(html);
					$elem.find('.ps-js-page-category-footer').show();
				})
				.fail(function (errors) {
					$elem.addClass(loadedClass);
					$elem.find('.ps-js-pages').html(errors.join('<br>'));
					$elem.find('.ps-js-page-category-footer').remove();
				});
		},

		_collapse: function ($elem) {
			$elem.removeClass('ps-pages__category--open');
		},

		_render_uncategorized: function () {
			var that = this;

			function render() {
				var id = '-1',
					name = peepsopagesdata.lang.uncategorized,
					url = peepsopagesdata.page_url.replace('##category_id##', id),
					categories = [{ id: id, name: name, url: url, __uncategorized: true }],
					html = that._search_render_html(categories);

				if (html) {
					that._search_$ct.append(html);
				}
			}

			return $.Deferred(function (defer) {
				var params = {
					uid: peepsodata.currentuserid,
					user_id: undefined,
					query: '',
					category: -1,
					order_by: undefined,
					order: undefined,
					admin: undefined,
					keys:
						'id,name,description,date_created_formatted,members_count,url,published,avatar_url_full,cover_url,privacy,pageuserajax.member_actions,pagefollowerajax.follower_actions',
					limit: 1,
					page: 1
				};

				ps_pages
					._fetch(params)
					.done(function () {
						render();
						defer.resolve();
					})
					.fail(function (errors) {
						defer.reject();
					});
			});
		}
	});

	return PsPagePagesCategories;
});
