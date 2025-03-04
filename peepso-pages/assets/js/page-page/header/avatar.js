import $ from 'jquery';
import { hooks } from 'peepso';
import { page as pageData } from 'peepsodata';
import avatarDialog from './avatar-dialog';

const GROUP_ID = +pageData.id;

$(function () {
	let $container = $('.ps-js-focus--page .ps-js-avatar');
	if (!$container.length) {
		return;
	}

	let $image = $container.find('.ps-js-avatar-image');
	let $buttonWrapper = $container.find('.ps-js-avatar-button-wrapper');

	hooks.addAction('page_avatar_updated', 'page_page', (id, imageUrl) => {
		if (+id === GROUP_ID) {
			$image.attr('src', imageUrl);
		}

		if (imageUrl.match(/\/pages\/\d+\//)) {
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
