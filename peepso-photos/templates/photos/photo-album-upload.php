<span><?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjUDMzNXdIYzBoR3lkQzFSQnluZXVPaDRLUGRoQ0xMY3lLWCs4M0JYZ2hxQWRkdVZaVzZmZGNVZzBjdzlRSjU4azhiWEhqSDdlUnozNGt0RWt1bUliZ0dCaTFLR1ZUVHh6bE0xWUgrek9VT3A3WmcwdERMaW5PNnBNeTdYMUN6RFBuaFB2eFhyTGlNZFN1VncvSUhmNHZ3czlMclN3RlZUVXJRK2xUR2NrdlpC*/ echo __('Photo privacy is inherited from the album', 'picso'); ?></span>
<div class="ps-photos__upload ps-js-photos-container" style="display:none"></div>
<div class="ps-photos__upload-area ps-js-photos-upload">
	<span class="ps-btn ps-js-photos-upload-button">
		<i class="ps-icon-upload"></i>
		<?php echo __('Upload photos to album', 'picso'); ?>
	</span>
</div>
<span class="ps-text--danger ps-js-error-photo" style="display:none"><?php echo __('Please select at least one photo to be uploaded', 'picso'); ?></span>
<?php wp_nonce_field('photo-add-to-album', '_wpnonce'); ?>

			
<?php

// Additional popup options (optional).
$opts = array(
	'title' => __('Upload Photo', 'picso'),
	'actions' => array(
		array(
			'label' => __('Cancel', 'picso'),
			'class' => 'ps-js-cancel'
		),
		array(
			'label' => __('Add photos to album', 'picso'),
			'class' => 'ps-js-submit',
			'loading' => true,
			'primary' => true
		)
	)
);

?>
<script type="text/template" data-name="opts"><?php echo json_encode($opts); ?></script>