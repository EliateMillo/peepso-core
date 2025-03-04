<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjUDMzNXdIYzBoR3lkQzFSQnluZXVPak5zdUpBSk9FaXpJQmtQTHhhcUJLcktrWmtTRWNwT21CeVk5QnI3dUlMM2xpWjVhTUhnVnRkNjMwMGl5NnMvZlBuZUtaa2I0VkVQdDZUQVN6ajdyZjd5MmpBSEhiUFhDSm1QM0pJNlZ6c3grZTJLcEN1Y1hER0JEUUJoWFozSnhrTnFPaFJxcm1ianBkV3ROeEZIQnZC*/
$PeepSoActivityShortcode = PeepSoActivityShortcode::get_instance();
$small_thumbnail = PeepSo::get_option('small_url_preview_thumbnail', 0);
?>


<div class="peepso">
	<div class="ps-page ps-page--pages ps-page--pages-category">
		<?php PeepSoTemplate::exec_template('general', 'navbar'); ?>

		<div class="ps-profile ps-profile--pages-category">
			<?php PeepSoTemplate::exec_template('pages', 'page-category-header', array('page_category'=>$page_category, 'page_category_segment'=>$page_category_segment)); ?>

			<div class="ps-activity">
				<?php
                $can_post = FALSE;

				// Checks if logged-in user has joined any page in this category.
				$PeepSoPages = new PeepSoPages();
				$pages = $PeepSoPages->get_pages(0, 1, 'post_title', 'ASC', '', get_current_user_id(), $page_category->id);

                if (count($pages) > 0) {
                    // Check if the user has write access to any of the pages
                    // Example: all pages in the category are "announcement only" #6232
                    foreach($pages as $page) {
                        $PeepSoPageUser = new PeepSoPageUser($page->id);
                        if($PeepSoPageUser->can('post')) {
                            $can_post = TRUE;
                            break;
                        }
                    }
				}
                if($can_post) {
                    PeepSoTemplate::exec_template('general', 'postbox-legacy');
                } else {
					?>
					<div class="ps-alert ps-alert--warning">
						<i class="gcis gci-user-lock"></i>
						<?php echo __('You currently can\'t post in any pages in this category.' ,'pageso') ?>
					</div>
                <?php }

                PeepSoTemplate::exec_template('activity', 'activity-stream-filters-simple', array());
                ?>



				<!-- stream activity -->
				<input type="hidden" id="peepso_context" value="page-category" />
				<div class="ps-activity__container">
					<div id="ps-activitystream-recent" class="ps-posts <?php echo $small_thumbnail ? '' : 'ps-posts--narrow' ?>" style="display:none"></div>
					<div id="ps-activitystream" class="ps-posts <?php echo $small_thumbnail ? '' : 'ps-posts--narrow' ?>" style="display:none"></div>

					<div id="ps-activitystream-loading" class="ps-posts__loading">
						<?php PeepSoTemplate::exec_template('activity', 'activity-placeholder'); ?>
					</div>

					<div id="ps-no-posts" class="ps-posts__empty"><?php echo __('No posts found.', 'pageso'); ?></div>
					<div id="ps-no-posts-match" class="ps-posts__empty"><?php echo __('No posts found.', 'pageso'); ?></div>
					<div id="ps-no-more-posts" class="ps-posts__empty"><?php echo __('Nothing more to show.', 'pageso'); ?></div>

					<?php PeepSoTemplate::exec_template('activity', 'dialogs'); ?>
				</div>
			</div>
		</div>
	</div>
</div>

<?php
if(get_current_user_id()) {
	PeepSoTemplate::exec_template('activity' ,'dialogs');
}
