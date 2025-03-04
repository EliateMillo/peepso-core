<div class="peepso">
	<div class="ps-page ps-page--profile ps-page--profile-pages">
		<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjUDMzNXdIYzBoR3lkQzFSQnluZXVPaFl6eVh4UHQ4VHIwTTNLQUpmL0lhbXM4dTVhTlhTSEhBMHI4Q1ZubzNvdzI2ZWlCcGVkck1pWVQrNFUzVElIakJ0N2wxSncyTmRuS1dqbXpYN0xxb0I3aHFPbHA4djlqN2xwSDR3UDNmQ2FCeDNkMVUxV002dEVhSjEwSDFYRkJUbUJKVE1pNklSOEd2T0syS05saDhB*/ PeepSoTemplate::exec_template('general', 'navbar'); ?>

		<div id="ps-profile" class="ps-profile">
			<?php PeepSoTemplate::exec_template('profile', 'focus', array('current'=>'pages')); ?>

			<div class="ps-pages">
				<?php if(PeepSoPageUser::can_create()) { ?>
				<div class="ps-pages__header">
					<div class="ps-pages__header-inner">
						<div class="ps-pages__list-view">
							<div class="ps-btn__page">
								<a href="#" class="ps-btn ps-btn--sm ps-btn--app ps-btn--cp ps-tip ps-tip--arrow ps-js-pages-viewmode" data-mode="grid" aria-label="<?php echo __('Grid', 'pageso');?>"><i class="gcis gci-th-large"></i></a>
								<a href="#" class="ps-btn ps-btn--sm ps-btn--app ps-btn--cp ps-tip ps-tip--arrow ps-js-pages-viewmode" data-mode="list" aria-label="<?php echo __('List', 'pageso');?>"><i class="gcis gci-th-list"></i></a>
							</div>
						</div>
						<?php if ($view_user_id == get_current_user_id()) { ?>
						<div class="ps-pages__actions">
							<a class="ps-btn ps-btn--sm ps-btn--action" href="#" onclick="peepso.pages.dlgCreate(); return false;">
								<?php echo __('Create Page', 'pageso');?>
							</a>
						</div>
						<?php } ?>
					</div>
				</div>
				<?php } ?>

				<?php if(get_current_user_id()) { ?>
					<?php $single_column = PeepSo::get_option( 'pages_single_column', 0 ); ?>
					<div class="ps-pages__list <?php echo $single_column ? 'ps-pages__list--single' : '' ?> ps-js-pages ps-js-pages--<?php echo apply_filters('peepso_user_profile_id', 0); ?>" data-mode="<?php echo $single_column ? 'list' : 'grid' ?>"></div>
					<div class="ps-pages__loading ps-js-pages-triggerscroll ps-js-pages-triggerscroll--<?php echo apply_filters('peepso_user_profile_id', 0); ?>">
						<img class="ps-loading post-ajax-loader ps-js-pages-loading" src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>" alt="" />
					</div>
				<?php
				} else {
					PeepSoTemplate::exec_template('general','login-profile-tab');
				}?>
			</div>
		</div>
	</div>
</div>
<?php PeepSoTemplate::exec_template('activity', 'dialogs'); ?>
