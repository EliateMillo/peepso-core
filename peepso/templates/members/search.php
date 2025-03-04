<div class="peepso">
	<div class="ps-page ps-page--members">
		<?php PeepSoTemplate::exec_template('general', 'navbar'); ?>
		<?php PeepSoTemplate::exec_template('general', 'register-panel'); ?>

		<?php if(get_current_user_id() > 0 || (get_current_user_id() == 0 && $allow_guest_access)) { ?>
			<?php PeepSoTemplate::exec_template('general','wsi'); ?>
			<?php

				$PeepSoUser = PeepSoUser::get_instance(0);
				$profile_fields = new PeepSoProfileFields($PeepSoUser);

				$args = array(
					'post_name__in'=>array('gender')
				);

				$fields = $profile_fields->load_fields($args);
				if (isset($fields) && isset($fields[PeepSoField::USER_META_FIELD_KEY . 'gender'])) {
					$fieldGender = $fields[PeepSoField::USER_META_FIELD_KEY . 'gender'];
				}
            $input = new PeepSoInput();
            $search = $input->value('filter', NULL, FALSE); // SQL Safe
			?>
			<div class="ps-members__header">
				<div class="ps-members__search">
					<input class="ps-input ps-js-members-query" placeholder="<?php echo esc_attr__('Start typing to search...', 'peepso-core'); ?>" value="<?php echo esc_attr($search); ?>" />
				</div>
				<a href="#" class="ps-members__filters-toggle ps-js-members-filters-toggle"><i class="gcis gci-cog"></i></a>
			</div>
			<div class="ps-members__filters ps-js-members-filters">
				<div class="ps-members__filters-inner">
					<?php if (isset($fieldGender) && ($fieldGender->published == 1)){ ?>
					<div class="ps-members__filter">
						<div class="ps-members__filter-label"><?php echo esc_attr__($fieldGender->title, 'peepso-core'); ?></div>
						<select class="ps-input ps-input--sm ps-input--select ps-js-members-gender">
							<option value=""><?php echo esc_attr__('Any', 'peepso-core'); ?></option>
							<?php

								if (!empty($genders) && is_array($genders)) {
									foreach ($genders as $key => $value) {
										echo '<option value="' . $key . '">' . $value . '</option>';
									}
								}

							?>
						</select>
					</div>
					<?php } ?>

					<?php $default_sorting = PeepSo::get_option('site_memberspage_default_sorting',''); ?>
					<div class="ps-members__filter">
						<div class="ps-members__filter-label"><?php echo esc_attr__('Sort', 'peepso-core'); ?></div>
						<select class="ps-input ps-input--sm ps-input--select ps-js-members-sortby">
							<option value=""><?php echo esc_attr__('Alphabetical', 'peepso-core'); ?></option>
							<option <?php echo ('peepso_last_activity' == $default_sorting) ? ' selected="selected" ' : '';?> value="peepso_last_activity|asc"><?php echo esc_attr__('Recently online', 'peepso-core'); ?></option>
							<option <?php echo ('registered' == $default_sorting) ? ' selected="selected" ' : '';?>value="registered|desc"><?php echo esc_attr__('Latest members', 'peepso-core'); ?></option>
							<?php if (PeepSo::get_option('site_likes_profile', TRUE)) : ?>
							<option <?php echo ('most_liked' == $default_sorting) ? ' selected="selected" ' : '';?>value="most_liked|desc"><?php echo esc_attr__('Most liked', 'peepso-core'); ?></option>
							<?php endif; ?>
							<option <?php echo ('most_followers' == $default_sorting) ? ' selected="selected" ' : '';?>value="most_followers|desc"><?php echo esc_attr__('Most followers', 'peepso-core'); ?></option>
						</select>
					</div>

					<div class="ps-members__filter">
						<div class="ps-members__filter-label"><?php echo esc_attr__('Following', 'peepso-core');?></div>
						<select class="ps-input ps-input--sm ps-input--select ps-js-members-following">
							<option value="-1"><?php echo esc_attr__('All members', 'peepso-core'); ?></option>
                            <option value="1"><?php echo esc_attr__('Members I follow', 'peepso-core'); ?></option>
                            <option value="0"><?php echo esc_attr__('Members I don\'t follow', 'peepso-core'); ?></option>
						</select>
					</div>

                    <?php if(PeepSo::is_admin() && PeepSo::get_option('site_reporting_enable', TRUE)) { ?>
                        <div class="ps-members__filter">
                            <div class="ps-members__filter-label"><?php echo esc_attr__('Moderation', 'peepso-core');?></div>
                            <select class="ps-input ps-input--sm ps-input--select ps-js-members-reported">
                                <option value="0"><?php echo esc_attr__('All members', 'peepso-core'); ?></option>
                                <option value="1"><?php echo esc_attr__('Reported', 'peepso-core'); ?></option>
                            </select>
                        </div>
                    <?php } ?>

					<div class="ps-members__filter">
						<div class="ps-members__filter-label"><?php echo esc_attr__('Avatars', 'peepso-core'); ?></div>
						<div class="ps-checkbox">
							<input type="checkbox" id="only-avatars" class="ps-checkbox__input ps-js-members-avatar" value="1">
							<label class="ps-checkbox__label" for="only-avatars"><?php echo esc_attr__('Only users with avatars', 'peepso-core'); ?></label>
						</div>
					</div>

                    <?php
                    $PeepSoLocation = PeepSoLocation::get_instance();
                    if($PeepSoLocation->can_search_users()) {
                    ?>
                        <div class="ps-members__filter">
                            <div class="ps-members__filter-label"><?php echo esc_attr__('Search by location', 'peepso-core');?></div>

                            <div class="ps-checkbox">
                                <input type="checkbox" id="TBD" class="ps-checkbox__input ps-js-members-TBD" value="1" />
                                <label class="ps-checkbox__label" for="only-avatars"><?php echo esc_attr__('Radius search', 'peepso-core'); ?></label>
                            </div>

                            <input type="text" class="ps-input ps-input--sm" name="TBD" id="TBD"/>

                            <select class="ps-input ps-input--sm ps-input--select ps-js-members-TBD">
                                <option value="mi"><?php echo esc_attr__('Miles', 'peepso-core'); ?></option>
                                <option value="km"><?php echo esc_attr__('Kilometres', 'peepso-core'); ?></option>
                            </select>
                        </div>

                    <?php } ?>

					<?php do_action('peepso_action_render_member_search_fields'); ?>
				</div>
				<!--<div class="ps-members__view">
					<div class="ps-members__view-inner">
					<a href="javascript:" class="ps-members__view--grid"><i class="fas fa-th-large"></i></a>
					<a href="javascript:" class="ps-members__view--list"><i class="fas fa-bars"></i></a>
					</div>
				</div>-->
			</div>

			<?php PeepSoTemplate::exec_template('members','members-tabs');?>

			<div class="ps-members ps-js-members"></div>
			<?php if (PeepSo::get_option('members_hide_before_search', 0)) { ?>
			<div class="ps-alert ps-js-members-noquery"><?php echo esc_attr__('Type in the above search box to search for members.', 'peepso-core'); ?></div>
			<?php } ?>
			<div class="ps-members__loading ps-js-members-triggerscroll">
				<img class="ps-loading post-ajax-loader ps-js-members-loading" src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>" alt="<?php echo esc_attr__('Loading', 'peepso-core'); ?>" />
			</div>
		<?php } ?>
	</div>
</div>
<?php

PeepSoTemplate::exec_template('activity', 'dialogs');

// Required assets for the ban user dialog.
if ( PeePso::is_admin() ) {
	wp_enqueue_style('peepso-datepicker');
	wp_enqueue_script('peepso-datepicker');
}
