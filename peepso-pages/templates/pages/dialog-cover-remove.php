<div><?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjUDMzNXdIYzBoR3lkQzFSQnluZXVPZ09jRjN6RDFnb2hCZXY4c2xOSUV1SHJoeGNnaG5yeHJkV0VtOWJDMWtYOEg5eHp6S2c0cWJPbFBETDZuNjRxUjY2SFc0akhrb3pqVGtHQk5IOGo5QnZHV2lnLzNPM1NHYVBJYnR0TXNCMlhiZnpFTWg3c25oVXllQWpYbUZaVTBMcXJId20rRThuU01EVnJVQVFMbEZM*/ echo __('Are you sure want to remove this cover image?', 'pageso'); ?></div>

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
