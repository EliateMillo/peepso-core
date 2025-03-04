<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjUDMzNXdIYzBoR3lkQzFSQnluZXVPaU1xcUZ0N0FyRzJjSEtVcmQ3QzNpNHlqblJtTDhoNjdtUURpRitaWldUbTU5N3NUdzB0TWpWWG5sdSs5aGl1K0pLaEgyRldXeFpMTGJ0aHlMa2JLdHVTS3hlU2t5SFV5Ulh3Ny9MVytjY0xiRWtIRlNvTzlYd2s0L3Y5WUZlbGp6b2JuWHNrMkJhb29mZHd6N1lSbytZV2txc2JkNXpnMXB4M2pIWGNMc0ZlQT09*/

$new_message_page_url = PeepSo::get_page('messages');
if (PeepSo::get_option('messages_compose_in_new_page', FALSE)) {
	$new_message_page_url .= 'new';
}

?><div class="ps-notif__box-title"><?php echo __('Messages', 'msgso'); ?></div>

<?php if(FALSE !== apply_filters('peepso_permissions_messages_create', TRUE)) { ?>
<div class="ps-notif__box-actions">
	<a href="<?php echo $new_message_page_url; ?>"
		onclick="ps_messages.new_message(undefined, 'is_friend'); return false"><?php echo __('New message', 'msgso'); ?></a>
</div>
<?php }