<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjUDMzNXdIYzBoR3lkQzFSQnluZXVPZ3BkK0s3b3MvNk9TVGdhQ0IxL2dQZHRpKzNTTWNVRVlPcFNPczZpVHU3MFlLTGdqcHBmc0NFcS8vRC8zeHlHSWpyTVZxa21udkF5eTdSaUcyOUVWMHMwMXVlS0pieEtuSDRsRTVCNnhTb2RkQnRSbGxkVmVSRmFsQjRnSUNUdXRzPQ==*/

$oPeepSoListTable = new PeepSoPagesListTable();
$oPeepSoListTable->prepare_items();

?>
<form id="form-mailqueue" method="post">
<?php

	wp_nonce_field('bulk-action', 'pages-nonce');
	echo $oPeepSoListTable->search_box(__('Search Pages', 'pageso'), 'search');
	$oPeepSoListTable->display();

?>
</form>
<script>
	// Add confirmation on delete.
	jQuery(function( $ ) {
		var evtName = 'submit.ps-pages',
			textConfirm = '<?php echo esc_js( __('Are you sure?', 'pageso') ); ?>';

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
