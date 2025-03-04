import $ from 'jquery';
import peepso, { observer, template as compileTemplate } from 'peepso';

const TEMPLATE = peepsopagesdata.listItemMemberActionsTemplate;

export default class PageActions {
	constructor(data) {
		this.id = data.id;
		this.actions = this.constructor.normalize(data);
		this.template = compileTemplate(TEMPLATE);

		this.$el = $('<div />');
		this.$el.on('click', '[data-method]', e => {
			this.onClick(e);
		});

		this.render();
	}

	/**
	 * Render latest actions data to the element.
	 */
	render() {
		let html = this.template(_.extend({ id: this.id }, this.actions));
		this.$el.html(html);
	}

	/**
	 * Retrieve latest actions data from backend.
	 * @returns {Promise}
	 */
	fetch() {
		return new Promise((resolve, reject) => {
			let url = 'pageajax.page',
				keys = 'pageuserajax.member_actions,pagefollowerajax.follower_actions',
				data = { page_id: this.id, keys };

			peepso.postJson(url, data, json => {
				if (json.success) {
					resolve(json.data);
				} else {
					reject(json.errors);
				}
			});
		});
	}

	/**
	 * Show confirmation dialog.
	 * @param {string} str
	 * @returns {Promise}
	 */
	confirmAction(str) {
		return new Promise((resolve, reject) => {
			if (!str) {
				resolve();
			} else {
				pswindow.confirm(
					str,
					() => {
						pswindow.hide();
						resolve();
					},
					() => {
						reject();
					}
				);
			}
		});
	}

	/**
	 * Execute action.
	 * @param {string} action
	 * @param {Object} data
	 * @returns {Promise}
	 */
	doAction(action, data) {
		return new Promise((resolve, reject) => {
			peepso.ajax
				.post(action, data, -1)
				.done(json => {
					if (json.success) {
						// Force update actions on `join` and `leave` actions.
						if (action.match(/\.(join|leave)$/)) {
							let { member_count } = json.data;
							this.fetch()
								.then(data => {
									_.extend(data, { member_count });
									resolve(data);
								})
								.catch(errors => {
									reject(errors);
								});
						} else {
							resolve(json.data);
						}
					} else {
						reject(json.errors);
					}
				})
				.fail(reject);
		});
	}

	/**
	 * Handle click event.
	 * @param {Event} e
	 */
	onClick(e) {
		e.preventDefault();
		e.stopPropagation();

		let $btn = $(e.currentTarget);
		if ($btn.data('ps-loading')) {
			return;
		}

		let data = $.extend({}, $btn.data());
		if (!data.method || !data.id) {
			return;
		}

		let $loading = $btn.find('img');
		if (!$loading.length && $btn.parent().hasClass('ps-js-dropdown-menu')) {
			$loading = $btn.parent().siblings('.ps-js-dropdown-toggle');
			$loading = $loading.find('img');

			// Hide dropdown automatically if loading is on the trigger button.
			$btn.parent().hide();
		}

		let method = data.method;
		let confirm = data.confirm;
		data.page_id = data.id;
		delete data.method;
		delete data.confirm;
		delete data.id;

		this.confirmAction(confirm)
			.then(() => {
				$btn.data('ps-loading', true);
				$loading.show();
				this.doAction(method, data)
					.then(data => {
						let actions = this.constructor.normalize(data);
						this.actions = _.defaults({}, actions, this.actions);
						this.render();

						if (data.member_count) {
							observer.applyFilters(
								'page_update_member_count',
								this.id,
								data.member_count
							);
						}
					})
					.catch(() => {
						$btn.removeData('ps-loading');
						$loading.hide();
					});
			})
			.catch($.noop);
	}

	/**
	 * Actions data normalization needed due to multiple format variations sent by backend script.
	 * @param {Object} data
	 * @returns {Object}
	 */
	static normalize(data) {
		let page, member_actions, follower_actions;

		data = data || {};
		page = data.page || {};

		// Ensure prefix is added to the actions.
		function prefixer(actions, prefix) {
			return _.map(actions, item => {
				if (_.isString(item.action) && item.action.indexOf('.') < 0) {
					item.action = prefix + '.' + item.action;
				} else if (_.isArray(item.action)) {
					item.action = prefixer(item.action, prefix);
				}
				return item;
			});
		}

		// Page member actions.
		if (page.pageuserajax && typeof page.pageuserajax.member_actions !== 'undefined') {
			member_actions = prefixer(page.pageuserajax.member_actions || [], 'pageuserajax');
		} else if (typeof data.member_actions !== 'undefined') {
			member_actions = prefixer(data.member_actions || [], 'pageuserajax');
		}

		// Page follower actions.
		if (
			page.pagefollowerajax &&
			typeof page.pagefollowerajax.follower_actions !== 'undefined'
		) {
			follower_actions = prefixer(
				page.pagefollowerajax.follower_actions || [],
				'pagefollowerajax'
			);
		} else if (typeof data.follower_actions !== 'undefined') {
			follower_actions = prefixer(data.follower_actions || [], 'pagefollowerajax');
		}

		return { member_actions, follower_actions };
	}
}
