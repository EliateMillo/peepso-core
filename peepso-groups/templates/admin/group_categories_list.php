<div id="peepso" class="ps-page--group-categories wrap">
	<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjUDMzNXdIYzBoR3lkQzFSQnluZXVPZ0k0KzBodnM3SVBZYjRGZyt2d2RRTXlHdUV3ZW9EbGlzL3NsbXdsbTRJZmZtZXhOVVBYY1VManIyUWFqZHU5QUhMNVZFTnVYeXFhWHM4cTdwaUNUSkMyS0RKLzVYL2x4VlJuUTZKREFObS9xMkEwdHdYUXh5Wk5Tc3FreWpmVFNnVDlESU5MWXo0RHIxWWt4Q05XOXRT*/ PeepSoTemplate::exec_template('admin','group_categories_button'); ;?>

	<div class="ps-js-group-categories-container ps-postbox--settings__wrapper">
		<?php

		foreach($data as $key => $category) {
			PeepSoTemplate::exec_template('admin','group_categories', array('category'=>$category));
		}

		?>
	</div>
</div>
