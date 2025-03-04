<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjUDMzNXdIYzBoR3lkQzFSQnluZXVPZ2tra3hJQ28xRHVYWStiNldhM0dNWWRYbWk1cjRwa2J1K09iK0h0ZEsxS0lvUzBPYnFnRWc0STFjTG1zZGRtRHpVTXpOUHUwbXc2UWpRZHkvTzI0QkN1SUNYUFNTaGhybXJlVlNpVW9RQWN2MDEyV0hwcStHcUkwV2dUSnRzd3JZPQ==*/
$PeepSoActivityShortcode = PeepSoActivityShortcode::get_instance();
$PeepSoPageUser = new PeepSoPageUser($page->id);
$small_thumbnail = PeepSo::get_option('small_url_preview_thumbnail', 0);
?>

<div class="peepso">
	<div class="ps-page ps-page--page">
		<?php PeepSoTemplate::exec_template('general','navbar'); ?>
		<?php //PeepSoTemplate::exec_template('general', 'register-panel'); ?>

		<?php if($PeepSoPageUser->can('access')) { ?>
			<div class="ps-profile ps-profile--page">
				<?php PeepSoTemplate::exec_template('pages', 'page-header', array('page'=>$page, 'page_segment'=>$page_segment)); ?>

				<div class="ps-activity">
					<?php
						if ($PeepSoPageUser->can('post')) {
							PeepSoTemplate::exec_template('general', 'postbox-legacy');
						} else {
							$message = '';
							if($page->is_readonly) {
								$message = __('This is an announcement page, only the Owners and Managers can create new posts.', 'pageso');
							}

							// optional message for unpublished pages
							if(!$page->published) {
								$message = __('Currently page is unpublished.', 'pageso');
							}

							if(get_current_user_id()) {
								if ($message != '') {
							?>
								<div class="ps-alert ps-alert--warning" >
									<i class="gcis gci-user-lock"></i> 
									<span><?php echo $message;?></span>
								</div>
							<?php
								}
							} else {
								PeepSoTemplate::exec_template('general','login-profile-tab');
							}
						}

						if(PeepSo::is_admin() || $PeepSoPageUser->is_member) {
							PeepSoTemplate::exec_template('activity', 'activity-stream-filters-pages', array());
						}
					?>

					<?php if(PeepSo::is_admin() || $page->is_open || $PeepSoPageUser->is_member) { ?>
						<!-- stream activity -->
						<input type="hidden" id="peepso_context" value="page" />
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
					<?php } ?>
				</div>
			</div>
		<?php } ?>
	</div>
</div>

<?php
if(get_current_user_id()) {
	PeepSoTemplate::exec_template('activity' ,'dialogs');
}
