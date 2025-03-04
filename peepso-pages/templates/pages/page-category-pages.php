<div class="peepso">
	<div class="ps-page ps-page--category-pages">
		<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjUDMzNXdIYzBoR3lkQzFSQnluZXVPak5zdUpBSk9FaXpJQmtQTHhhcUJLcmFpZnlUOVd2QlFnWTlvMDhxZ0pWaGpjVnd5MkxyYk5ndkxZRXhHNlRZYzcwb1NzSGJhRWVua2NvRXdIaWdsMTZOT2hHVFhlcVlHeG1aTmtOWExVci9TNm90Ymp5SVNpTVpjSWlFZTVhUXZIdkdISjVZSGc1Nmljd2tDcXZiSjJH*/ PeepSoTemplate::exec_template('general','navbar'); ?>
		<div class="ps-pages">
			<?php PeepSoTemplate::exec_template('pages', 'page-category-header', array('page_category'=>$page_category, 'page_category_segment'=>$page_category_segment)); ?>

			<div class="ps-pages__header">
				<div class="ps-pages__header-inner">
					<div class="ps-pages__list-view">
						<div class="ps-btn__page">
							<a href="#" class="ps-btn ps-btn--sm ps-btn--app ps-btn--cp ps-tip ps-tip--arrow ps-js-pages-viewmode" data-mode="grid" aria-label="<?php echo __('Grid', 'pageso');?>"><i class="gcis gci-th-large"></i></a>
							<a href="#" class="ps-btn ps-btn--sm ps-btn--app ps-btn--cp ps-tip ps-tip--arrow ps-js-pages-viewmode" data-mode="list" aria-label="<?php echo __('List', 'pageso');?>"><i class="gcis gci-th-list"></i></a>
						</div>
					</div>

					<?php if(PeepSoPageUser::can_create()) { ?>
					<div class="ps-pages__actions">
						<a class="ps-btn ps-btn--sm ps-btn--action" href="#" onclick="peepso.pages.dlgCreate(); return false;">
							<?php echo __('Create Page', 'pageso');?>
						</a>
					</div>
					<?php } ?>
				</div>
			</div>

			<input type="hidden" class="ps-js-pages-category" value="<?php echo $page_category->id ?>">
			<input type="hidden" class="ps-js-pages-sortby" value="post_title">
			<input type="hidden" class="ps-js-pages-sortby-order" value="ASC">

			<div class="mb-20"></div>
			<?php $single_column = PeepSo::get_option( 'pages_single_column', 0 ); ?>
			<div class="ps-pages__list <?php echo $single_column ? 'ps-pages__list--single' : '' ?> ps-js-pages" data-mode="<?php echo $single_column ? 'list' : 'grid' ?>"></div>
			<div class="ps-pages__loading ps-js-pages-triggerscroll">
				<img class="ps-loading post-ajax-loader ps-js-pages-loading" src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>" alt="" />
			</div>
		</div>
	</div>
</div>
<?php PeepSoTemplate::exec_template('activity', 'dialogs'); ?>
