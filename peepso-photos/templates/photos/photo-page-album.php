<div class="peepso ps-page-profile ps-page--group">
	<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjUDMzNXdIYzBoR3lkQzFSQnluZXVPaVBWU2daR3RsbmtRTnN5Yk42MVBhZmQ1MDJVRTVQdVdneGZQY1BDZzExRkFJeDB2dXVWNnd3NFh3MXY0dmJrYmd3U2F2WDBodGx5Y2d6cTBkY25EaVBMYm5aU01QNzU3cU1FS01Vby84MENVQjE0dWVNTFFIR0UyS3pRTFIvcEo5VFp2MW5OMUN3SStlVXp1eXBwWi9F*/ PeepSoTemplate::exec_template('general', 'navbar'); ?>
	<?php //PeepSoTemplate::exec_template('general', 'register-panel'); ?>

	<?php $PeepSoPageUser = new PeepSoPageUser($page->id, get_current_user_id());?>
	<?php if($PeepSoPageUser->can('access')) { ?>

	<?php PeepSoTemplate::exec_template('pages', 'page-header', array('page'=>$page, 'page_segment'=>$page_segment)); ?>

	<div class="ps-album">
		<?php if (! get_current_user_id()) { PeepSoTemplate::exec_template('general','login-profile-tab'); } ?>

		<div class="ps-album__header">
			<div class="ps-album__title">
				<i class="gcis gci-images"></i><?php echo sprintf (__('%s Album', 'picso'), __($the_album->pho_album_name, 'picso')); ?>
			</div>

			<div class="ps-album__actions">
				<a class="ps-btn ps-btn--sm" href="<?php echo $photos_url; ?>"><i class="gcis gci-angle-left"></i><span><?php echo __('Back to Photos', 'picso'); ?></span></a>
			</div>
		</div>

		<div class="ps-album__filters">
			<div class="ps-album__list-view">
				<div class="ps-btn__group">
					<a href="#" class="ps-btn ps-btn--sm ps-btn--app ps-btn--cp ps-tip ps-tip--arrow ps-tip--inline ps-js-photos-viewmode" data-mode="small" aria-label="<?php echo __('Small thumbnails', 'picso');?>"><i class="gcis gci-th"></i></a>
					<a href="#" class="ps-btn ps-btn--sm ps-btn--app ps-btn--cp ps-tip ps-tip--arrow ps-tip--inline ps-js-photos-viewmode" data-mode="large" aria-label="<?php echo __('Large thumbnails', 'picso');?>"><i class="gcis gci-th-large"></i></a>
				</div>
			</div>

			<select class="ps-input ps-input--sm ps-input--select ps-js-photos-sortby">
				<option value="desc"><?php echo __('Newest first', 'picso');?></option>
				<option value="asc"><?php echo __('Oldest first', 'picso');?></option>
			</select>
		</div>

		<div class="mb-20"></div>
		<div class="ps-photos__list ps-js-photos ps-js-photos--<?php echo  apply_filters('peepso_user_profile_id', 0); ?>"></div>
		<div class="ps-scroll ps-js-photos-triggerscroll ps-js-photos-triggerscroll--<?php echo  apply_filters('peepso_user_profile_id', 0); ?>">
			<img class="post-ajax-loader ps-js-photos-loading" src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>" alt="" style="display:none" />
		</div>
		<div class="mb-20"></div>
	</div>
	<?php } ?>
</div><!--end row-->
<?php PeepSoTemplate::exec_template('activity', 'dialogs'); ?>
