import $ from 'jquery';
import { hooks } from 'peepso';
import { page_category as categoryData } from 'peepsodata';
import avatarDialog from './avatar-dialog';

const CATEGORY_ID = +categoryData.id;

$(function () {
	let $container = $('.ps-js-focus--page-category .ps-js-avatar');
	if (!$container.length) {
		return;
	}

	let $image = $container.find('.ps-js-avatar-image');
	let $buttonWrapper = $container.find('.ps-js-avatar-button-wrapper');

	hooks.addAction('page_category_avatar_updated', 'page_page_category', (id, imageUrl) => {
		if (+id === CATEGORY_ID) {
			$image.attr('src', imageUrl);
		}

		if (imageUrl.match(/\/page-categories\/\d+\//)) {
			// Custom avatar.
			$buttonWrapper.css('cursor', '');
			$buttonWrapper.attr(
				'onclick',
				`peepso.simple_lightbox('${imageUrl.replace('-full.', '-orig.')}'); return false`
			);
		} else {
			// Default avatar.
			$buttonWrapper.css('cursor', 'default');
			$buttonWrapper.removeAttr('onclick');
		}
	});

	let $button = $container.find('.ps-js-avatar-button');
	$button.on('click', e => {
		e.preventDefault();
		e.stopPropagation();

		avatarDialog().show();
	});
});
