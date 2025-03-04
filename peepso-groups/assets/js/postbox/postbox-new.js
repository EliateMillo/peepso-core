import $ from 'jquery';
import _ from 'underscore';
import { hooks, template } from 'peepso';
import {
	ajaxurl_legacy as AJAXURL_LEGACY,
	peepso_nonce as PEEPSO_NONCE,
	currentuserid as LOGIN_USER_ID,
	groups as groupsData
} from 'peepsodata';

const GROUP_ID = window.peepsogroupsdata && +peepsogroupsdata.group_id;
const MODULE_ID = window.peepsogroupsdata && +peepsogroupsdata.module_id;

// Add groups parameter if postbox is on the group page.
hooks.addFilter('postbox_data', (data, postbox) => {
	if (GROUP_ID) {
		data.module_id = MODULE_ID;
		data.group_id = GROUP_ID;
	}

	return data;
});

// Open the group selector in group category page if no one is selected.
hooks.addFilter(
	'postbox_validate',
	(valid, postbox, data) => {
		// Find the group category here, as it might not available upon load.
		let categoryId = window.peepsoGroupCategory && +peepsoGroupCategory.id;

		if (categoryId && !(data.module_id && data.group_id)) {
			valid = false;
		}

		return valid;
	},
	100 // Set priority to a high number to prevent it being overwritten.
);

// Postbox dropdown options.

hooks.addAction('postbox_option_init', (postboxOption, postbox) => {
	if ('undefined' === typeof postboxOption.postTo) return;

	let $container = postboxOption.$dropdown.find('[data-value=group]');
	if (!$container.length) return;

	// Find the group category here, as it might not available upon load.
	let categoryId = window.peepsoGroupCategory && +peepsoGroupCategory.id;

	let $query = $container.find('[name=query]');
	let $loading = $container.find('.ps-js-loading');
	let $result = $container.find('.ps-js-result');
	let $list = $container.find('.ps-js-result-list');
	let itemTemplate = template($container.find('.ps-js-result-item-template').text());
	let searchToken;
	let searchXhr;

	function search(query = '') {
		let data = {
			uid: LOGIN_USER_ID,
			user_id: LOGIN_USER_ID,
			category: categoryId,
			query: query.trim(),
			writable_only: 1,
			keys: 'id,name,description,avatar_url_full,privacy,groupuserajax.can_pin_posts',
			order_by: 'post_title',
			order: 'ASC',
			limit: 40,
			page: 1
		};

		return $.ajax({
			url: `${AJAXURL_LEGACY}groupsajax.search`,
			type: 'GET',
			dataType: 'json',
			beforeSend: xhr => xhr.setRequestHeader('X-PeepSo-Nonce', PEEPSO_NONCE),
			data
		});
	}

	function render(data) {
		let html = `<i>${groupsData.textNoResult}</i>`;
		if (data.groups && data.groups.length) {
			html = data.groups.map(item => itemTemplate(item));
		}

		$list.html(html);
	}

	$query.one('focus', () => $query.trigger('input'));
	$query.on('input', e => {
		let token = (searchToken = Date.now());

		$list.empty();
		$result.hide();
		$loading.show();

		searchXhr && searchXhr.abort();
		searchXhr = search($query.val().trim())
			.always(() => (searchXhr = null))
			.done(json => {
				if (token === searchToken) {
					$loading.hide();
					$result.show();

					if (json && json.data) {
						render(json.data);
					}
				}
			});
	});
});
