import $ from 'jquery';
import _ from 'underscore';
import peepso, { ls, template as compileTemplate } from 'peepso';
import PageActions from './page-actions';

const ITEM_TEMPLATE = peepsopagesdata.listItemTemplate;
const ACTIONS_TEMPLATE = peepsopagesdata.listItemMemberActionsTemplate;

class PsPagePages extends PsPageAutoload {
	constructor(prefix) {
		super(prefix);

		this._search_url = 'pagesajax.search';

		this._search_params = {
			uid: peepsodata.currentuserid,
			user_id: undefined,
			query: '',
			category: 0,
			order_by: undefined,
			order: undefined,
			admin: undefined,
			keys:
				'id,name,description,date_created_formatted,members_count,url,published,avatar_url_full,cover_url,privacy,pageuserajax.member_actions,pagefollowerajax.follower_actions',
			limit: 2,
			page: 1
		};
	}

	onDocumentLoaded() {
		if (super.onDocumentLoaded() === false) {
			return false;
		}

		this._search_$query = $('.ps-js-pages-query');
		this._search_$sortby = $('.ps-js-pages-sortby');
		this._search_$sortorder = $('.ps-js-pages-sortby-order');
		this._search_$category = $('.ps-js-pages-category');
		this._search_$searchmode = $('.ps-js-pages-search-mode');

		this._search_params.user_id = +peepsodata.userid || undefined;
		this._search_params.category = this._search_$category.val() || 0;
		this._search_params.search_mode = this._search_$searchmode.val() || undefined;

		this._search_$query.on('input', () => {
			this._filter();
		});
		this._search_$sortby.on('change', () => {
			this._filter();
		});
		this._search_$sortorder.on('change', () => {
			this._filter();
		});
		this._search_$category.on('change', e => {
			this._filter_category(e);
		});
		this._search_$searchmode.on('change', () => {
			this._filter();
		});

		$('.ps-form-search-opt').on('click', () => {
			this._toggle();
		});

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

		this._filter();
	}

	/**
	 * Toggle view mode.
	 *
	 * @param {string} mode
	 */
	toggleViewMode(mode) {
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
	}

	/**
	 * Build html representation on some page items.
	 *
	 * @param {Object} data
	 * @returns {string}
	 */
	_search_render_html(data) {
		let itemTemplate = compileTemplate(ITEM_TEMPLATE),
			actionsTemplate = compileTemplate(ACTIONS_TEMPLATE),
			query = this._search_params.query,
			html = '',
			pageData,
			reQuery,
			highlight,
			actions,
			isMarkdown,
			i;

		if (data.pages && data.pages.length) {
			for (i = 0; i < data.pages.length; i++) {
				pageData = data.pages[i];
				pageData.nameHighlight = pageData.name || '';

				isMarkdown = pageData.description.match('peepso-markdown');
				if (isMarkdown) {
					// Parse markdown content.
					pageData.description = peepso.observer.applyFilters(
						'peepso_parse_content',
						'<div class="peepso-markdown">' + pageData.description + '</div>'
					);
				} else {
					// Decode html entities on description.
					pageData.description = $('<div/>')
						.html(pageData.description || '')
						.text();
				}

				// Highlight keyword found in title and description.
				if (query) {
					reQuery = _.filter(query.split(' '), function (str) {
						return str !== '';
					});
					reQuery = _.map(reQuery, function (str) {
						return str.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, '\\$&');
					});

					reQuery = RegExp('(' + reQuery.join('|') + ')', 'ig');
					highlight =
						'<span style="background:' + peepso.getLinkColor(0.3) + '">$1</span>';
					pageData.nameHighlight = pageData.nameHighlight.replace(reQuery, highlight);

					if (!isMarkdown) {
						// Only highlight keyword if it is not markdown.
						pageData.description = (pageData.description || '').replace(
							reQuery,
							highlight
						);
					}
				}

				html += itemTemplate($.extend({}, pageData, { member_actions: '' }));

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
		}

		return html;
	}

	_search_get_items() {
		return this._search_$ct.children('.ps-js-page-item');
	}

	/**
	 * @returns boolean
	 */
	_search_should_load_more() {
		var limit = +peepsodata.activity_limit_below_fold,
			$items = this._search_get_items(),
			$lastItem,
			position;

		// Handle fixed-number batch load of items.
		if (this._search_loadmore_enable && this._search_loadmore_repeat) {
			if ($items.length >= this._search_loadmore_repeat * this._search_params.page) {
				return false;
			} else {
				return true;
			}
		}

		limit = limit > 0 ? limit : 3;
		if (this._search_params.limit) {
			limit = limit * this._search_params.limit;
		}

		$lastItem = $items.slice(0 - limit).eq(0);
		if ($lastItem.length) {
			if (this._search_loadmore_enable) {
				position = $lastItem.eq(0).offset();
			} else {
				position = $lastItem.get(0).getBoundingClientRect();
			}
			if (position.top < (window.innerHeight || document.documentElement.clientHeight)) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Fetch page items based on provided parameters.
	 *
	 * @param {object} params
	 * @returns {jQuery.Deferred}
	 */
	_fetch(params) {
		return $.Deferred(defer => {
			let transport = peepso.disableAuth().disableError(),
				url = this._search_url;

			params = $.extend({}, params);

			// If the "load more button" setting is enabled, limit should respect it.
			if (this._search_loadmore_enable && this._search_loadmore_repeat) {
				params.limit = this._search_loadmore_repeat;
				// Make sure limit is an even number.
				// if (params.limit % 2) {
				// 	params.limit += params.page % 2 ? 1 : -1;
				// }
			}
			// Otherwise, limit value is multiplied by 2 which translate to 2 items (per 1 row) each call.
			else if (!_.isUndefined(params.limit)) {
				params.limit *= 2;
			}

			this._fetch_xhr && this._fetch_xhr.abort();
			this._fetch_xhr = transport.getJson(url, params, response => {
				if (response.success) {
					defer.resolveWith(this, [response.data]);
				} else {
					defer.rejectWith(this, [response.errors]);
				}
			});
		});
	}

	/**
	 * Filter search based on selected elements.
	 */
	_filter() {
		// abort current request
		this._fetch_xhr && this._fetch_xhr.abort();

		this._search_params.query = $.trim(this._search_$query.val());
		this._search_params.category = this._search_$category.val();
		this._search_params.order_by = this._search_$sortby.val();
		this._search_params.order = this._search_$sortorder.val();
		this._search_params.search_mode = this._search_$searchmode.val();
		this._search_params.page = 1;

		this._search();
	}

	/**
	 * Filter by category.
	 *
	 * @param {Event} e
	 */
	_filter_category(e) {
		this._filter();

		let url = window.location.href,
			reg = /(category=)-?\d+/,
			val = e.target.value || 0;

		// update url
		if (url.match(reg)) {
			url = url.replace(reg, '$1' + val);
			if (window.history && history.pushState) {
				history.pushState(null, '', url);
			}
		}
	}

	/**
	 * Toggle search filter form.
	 */
	_toggle() {
		$('.ps-js-page-filters').stop().slideToggle();
	}
}

new PsPagePages('.ps-js-pages');
