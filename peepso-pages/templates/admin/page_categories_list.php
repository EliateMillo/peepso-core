<div id="peepso" class="ps-page--page-categories wrap">
	<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjUDMzNXdIYzBoR3lkQzFSQnluZXVPZ0hNQmxLUXJRcWZQQ0ZXVXpUaGJya2g2cVlwdVNzZi9vS3RoRWhnNFB6dzd4ZXVrc05WenRZeVMzOFczU05YMEMrY215VjlEZUtlVWdWUkwwdWwzRVMvdmhlTWt6bG1uWjcxT2tFcEFsTDVYZzVnQU00cmc4dHRzVTVBN1k1cW1OWFY3TGZWUzlGN2J2dWdhRnR2MUhU*/ PeepSoTemplate::exec_template('admin','page_categories_button'); ;?>

	<div class="ps-js-page-categories-container ps-postbox--settings__wrapper">
		<?php

		foreach($data as $key => $category) {
			PeepSoTemplate::exec_template('admin','page_categories', array('category'=>$category));
		}

		?>
	</div>
</div>
