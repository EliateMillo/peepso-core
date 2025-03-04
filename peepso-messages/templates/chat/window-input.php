<div>
	<div class="ps-chat__window-input-wrapper ps-js-attachment" style="display:none"></div>
	<div class="ps-chat__window-input-wrapper">
		<textarea class="ps-chat__window-input" placeholder="<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjUDMzNXdIYzBoR3lkQzFSQnluZXVPajhRRWhVU2NNMUxaUTU0SkdLSmpadU1hMERYdGh3dlhhOHJjaEpPcjRFRjJGTGZOVThTU0hPMUJ1SExRQytLTVVUY1NVTldtZk1veUdscmlxVTRRMGtxMG1BN3VxSUZmNUpJVHoyR3A4Vk9GSnl1QjBhNTN5cDZSRlNTbXBKTzhpd2cwVlBpYVdIeVNEaEs4Q0NibVNG*/ echo __('Write a message...', 'msgso'); ?>"></textarea>
		<?php if ( count($addons) > 0 ) { ?>
		<div class="ps-chat__window-input-addons ps-chat-input-addons ps-js-addons">
			<?php foreach( $addons as $addon ) { ?>
			<?php echo $addon; ?>
			<?php } ?>
		</div>
		<?php } ?>
	</div>
</div>