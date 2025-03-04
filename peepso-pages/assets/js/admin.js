jQuery(function ($) {
	let $pinPageOnly = $('input[name=pages_pin_page_only]');

	// Toggle file upload configs.
	$pinPageOnly.on('click', function () {
		let $fields = $('input[name=pages_pin_page_only_no_pinned_style]').closest('.form-page');
		this.checked ? $fields.show() : $fields.hide();
	});

	$pinPageOnly.triggerHandler('click');
});
