<div class="ps-settings__bar clearfix">
	<div class="ps-settings__nav">
		<button type="button" class="ps-js-page-categories-expand-all">
			<i class="fa fa-expand"></i> <span><?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjUDMzNXdIYzBoR3lkQzFSQnluZXVPZ0hNQmxLUXJRcWZQQ0ZXVXpUaGJyazVsUlhpTFhja284SHU4ZkloNUtrU1lmTXN3enJJRGhidjBneUpJbU52ODBoczdrZ01vdXcwbGl4bllzMkhFQVhmQ3JCcTlDUXphSWRjbFVyL1AwaUI3ZkU4U2loNGdvN3hwbndmSDdMYnVkOWJINHNuUXpyRFZqalp0d2Z1d2NW*/ echo __('Expand All', 'pageso'); ?></span>
		</button>
		<button type="button" class="ps-js-page-categories-collapse-all">
			<i class="fa fa-compress"></i> <span><?php echo __('Collapse All', 'pageso'); ?></span>
		</button>
	</div>
	<?php
	if(!PeepSo::get_option('pages_categories_enabled', FALSE)) {
		echo '<span class="ps-settings__bar-notice"><a href="'.admin_url('admin.php?page=peepso_config&tab=pages#field_pages_categories_enabled').'">' . __('Page categories are currently disabled. You can enable them in PeepSo Config -> Pages', 'pageso') . '</a></span>';
	}

	?>
	<div class="ps-settings__nav ps-settings__nav--right ps-dropdown">
		<button type="button" class="btn-primary ps-js-page-categories-new">
			<i class="fa fa-plus"></i> <span><?php echo __('Add New', 'pageso'); ?></span>
		</button>
	</div>
</div>

<?php
if(!PeepSo::get_option('pages_categories_enabled', FALSE)) {
	echo '<div class="ps-alert ps-hide--desktop">', sprintf(__('Page categories are currently disabled. You can enable them in PeepSo Config -> Pages', 'pageso')), '</div>';
}

?>
