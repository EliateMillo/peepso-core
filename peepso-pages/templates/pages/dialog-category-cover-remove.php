<div><?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjUDMzNXdIYzBoR3lkQzFSQnluZXVPaW5JWUNRallJeVdKUjRNcm92VnlZekpXb0JLTEtLVjNCS3BmRjV4ajJ1bHJtNEF6ZTYwck9nWUllVnFZckcrVjBvdC8rbm00T210YzU2aXVGKzJmMEpaYWU1cVBQRkxzS290bk5BMTBCNFBmeEFUNkl6ZlBxTXVuL2xoMlQ2S1JrcklQRkg4UmdGcUZMWFRGeUduRTI3K05rbHY3ZXFUbytFM2VTNWxLb3R4QT09*/ echo __('Are you sure want to remove this cover image?', 'pageso'); ?></div>

<?php

// Additional popup options (optional).
$opts = array(
	'title' => __('Remove Cover Image', 'pageso'),
	'actions' => array(
		array(
			'label' => __('Cancel', 'pageso'),
			'class' => 'ps-js-cancel'
		),
		array(
			'label' => __('Confirm', 'pageso'),
			'class' => 'ps-js-submit',
			'loading' => true,
			'primary' => true
		)
	)
);

?>
<script type="text/template" data-name="opts"><?php echo json_encode($opts); ?></script>
