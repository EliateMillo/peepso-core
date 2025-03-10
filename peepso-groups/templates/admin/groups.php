<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjUDMzNXdIYzBoR3lkQzFSQnluZXVPaVpiVEVnVWpaSW5ReUZKaTYvYUNxNUNURjFTWWNoV3Rla0U3TGtJcjBCbkUyTG9tTHJXeVR6YWtmY0IxK2w5cExjS0Uwa2R1UWJtem10Wk9Ga01PUEpVYmY2WDA1dXA4TG9TZWZLMTcydjIvN0Z3ZTBERFppd1l4Um56WkpnVEUwPQ==*/

$oPeepSoListTable = new PeepSoGroupsListTable();
$oPeepSoListTable->prepare_items();

?>
<form id="form-mailqueue" method="post">
<?php

	wp_nonce_field('bulk-action', 'groups-nonce');
	echo $oPeepSoListTable->search_box(__('Search Groups', 'groupso'), 'search');
	$oPeepSoListTable->display();

?>
</form>
<script>
	// Add confirmation on delete.
	jQuery(function( $ ) {
		var evtName = 'submit.ps-groups',
			textConfirm = '<?php echo esc_js( __('Are you sure?', 'groupso') ); ?>';

		$( '#form-mailqueue' ).on( evtName, function( e ) {
			var $form = $( this ),
				$sel1 = $form.find( '[name=action]' ),
				$sel2 = $form.find( '[name=action2]' );

			if ( $sel1.val() === 'delete' || $sel2.val() === 'delete' ) {
				e.preventDefault();
				e.stopPropagation();
				if ( window.confirm( textConfirm ) ) {
					$form.off( evtName ).submit();
				}
			}
		});
	});
</script>
