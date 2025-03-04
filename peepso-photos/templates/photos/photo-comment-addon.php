<div class="ps-comments__input-addon ps-comments__input-addon--photo ps-js-addon-photo">
	<img class="ps-js-img" alt="photo"
		src="<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjUDMzNXdIYzBoR3lkQzFSQnluZXVPaEZCKytuOVozODAxMXZJOWpObk9TSWRpQ2s3a1JvL25PNGJLYmgwaUdBNCtxWUh0R2MvZHNDYVJFaXBQTTJjYnNZNUIzaXQ0MEdqekxxWmFFd1ZraUY2eGt5azhIZk1ISjQ4V1A5T0U0bkJqVkRiMitqMVpkNFh6V1pscHlod2NHTTREL1AyZzFyM0dHNkFBUjhvTC8y*/ echo isset($thumb) ? $thumb : ''; ?>"
		data-id="<?php echo isset($id) ? $id : ''; ?>" />

	<div class="ps-loading ps-js-loading">
		<img src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>" alt="loading" />
	</div>

	<div class="ps-comments__input-addon-remove ps-js-remove">
		<?php wp_nonce_field('remove-temp-files', '_wpnonce_remove_temp_comment_photos'); ?>
		<i class="gcis gci-times"></i>
	</div>
</div>
