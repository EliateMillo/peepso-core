<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjUDMzNXdIYzBoR3lkQzFSQnluZXVPZ3BkK0s3b3MvNk9TVGdhQ0IxL2dQZHRpKzNTTWNVRVlPcFNPczZpVHU3MFlLTGdqcHBmc0NFcS8vRC8zeHlHSWpyTVZxa21udkF5eTdSaUcyOUVWMHMwMXVlS0pieEtuSDRsRTVCNnhTb2RkQnRSbGxkVmVSRmFsQjRnSUNUdXRzPQ==*/

$categories_enabled = FALSE;
$categories_tab  = FALSE;

if(PeepSo::get_option('pages_categories_enabled', FALSE)) {

	$categories_enabled = TRUE;

	$PeepSoPageCategories = new PeepSoPageCategories(FALSE, NULL);
	$categories = $PeepSoPageCategories->categories;
	if (!isset($_GET['category'])) {
		$categories_default_view = PeepSo::get_option('pages_categories_default_view', 0);
		$_GET['category'] = $categories_default_view;
	}

	if (!isset($_GET['category']) || (isset($_GET['category']) && intval($_GET['category'])==1)) {
		$categories_tab = TRUE;
	}
}
?>
<div class="peepso">
	<div class="ps-page ps-page--pages">
		<?php PeepSoTemplate::exec_template('general','navbar'); ?>
		<?php PeepSoTemplate::exec_template('general', 'register-panel'); ?>

		<div class="ps-pages">
			<?php if(get_current_user_id() || (get_current_user_id() == 0 && $allow_guest_access)) { ?>
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


                    <?php if(!$categories_tab) { ?>
                        <div class="ps-pages__search">
                            <form class="ps-form" role="form" name="form-peepso-search" onsubmit="return false;">
                                <div class="ps-form__row">
                                    <div class="ps-form__field ps-form__field--icon">
										<i class="gcis gci-search"></i>
                                        <input placeholder="<?php echo __('Start typing to search...', 'pageso');?>" type="text" class="ps-input ps-input--sm ps-input--icon ps-input--icon-right ps-pages__search-input ps-js-pages-query" name="query" value="<?php echo esc_attr($search); ?>" />
                                        <a href="#" class="ps-input__icon ps-input__icon--right ps-pages__filters-toggle ps-tooltip ps-form-search-opt" onclick="return false;" data-tooltip="<?php echo __('Show filters', 'pageso');?>">
                                            <i class="gcis gci-cog"></i>
                                        </a>
                                    </div>
                                    <div class="ps-pages__filter">
                                        <select class="ps-input ps-input--sm ps-input--select ps-js-pages-search-mode">
                                            <option value="exact"><?php echo __('Exact phrase', 'peepso-core'); ?></option>
                                            <option value="any"><?php echo __('Any of the words', 'peepso-core'); ?></option>
                                        </select>
                                    </div>
                                </div>



                            </form>
                        </div>



						<?php
						$default_sorting = '';
						if(!strlen(esc_attr($search)))
						{
							$default_sorting = PeepSo::get_option('pages_default_sorting','id');
							$default_sorting_order = PeepSo::get_option('pages_default_sorting_order','DESC');
						}
						?>

						<div class="ps-pages__filters ps-js-page-filters" style="<?php echo ($categories_enabled && !$categories_tab) ? "" : "display:none";?>">
                            <?php
                            #6666 GeoMyWP hooks
                            do_action('peepso_action_render_pages_search_before');
                            ?>
							<div class="ps-pages__filters-inner">
								<div class="ps-pages__filter">
									<label class="ps-pages__filter-label"><?php echo __('Sort', 'pageso'); ?></label>
									<select class="ps-input ps-input--sm ps-input--select ps-js-pages-sortby">
											<option value="id"><?php echo __('Recently added', 'pageso'); ?></option>
											<option <?php echo ('post_title' == $default_sorting) ? ' selected="selected" ' : '';?> value="post_title"><?php echo __('Alphabetical', 'pageso'); ?></option>
											<option <?php echo ('meta_members_count' == $default_sorting) ? ' selected="selected" ' : '';?>value="meta_members_count"><?php echo __('Followers count', 'pageso'); ?></option>
									</select>
								</div>

								<div class="ps-pages__filter">
									<label class="ps-pages__filter-label">&nbsp;</label>
									<select class="ps-input ps-input--sm ps-input--select ps-js-pages-sortby-order">
											<option value="DESC"><?php echo __('Descending', 'pageso'); ?></option>
											<option <?php echo ('ASC' == $default_sorting_order) ? ' selected="selected" ' : '';?> value="ASC"><?php echo __('Ascending', 'pageso'); ?></option>
									</select>
								</div>

								<?php if($categories_enabled) { ?>
									<div class="ps-pages__filter">
										<label class="ps-pages__filter-label"><?php echo __('Category', 'pageso'); ?></label>
										<select class="ps-input ps-input--sm ps-input--select ps-js-pages-category">
											<option value="0"><?php echo __('No filter', 'pageso'); ?></option>
											<?php
											if(count($categories)) {
												foreach($categories as $id=>$cat) {
														$count = PeepSoPageCategoriesPages::update_stats_for_category($id);
													$selected = "";
													if($id==$category) {
														$selected = ' selected="selected"';
													}
													echo "<option value=\"$id\"{$selected}>{$cat->name} ($count)</option>";
												}
											}

											$count_uncategorized = PeepSoPageCategoriesPages::update_stats_for_category();
											if ($count_uncategorized > 0) {
												?>
												<option value="-1" <?php if(-1 == $category) { echo 'selected="selected"';}?>><?php echo __('Uncategorized', 'pageso'); ?></option>
												<?php
											}
											?>
										</select>
									</div>
								<?php } // ENDIF ?>



							</div>
                            <?php
                            #6666 GeoMyWP hooks
                            do_action('peepso_action_render_pages_search_after');
                            ?>
						</div>
					<?php } ?>
				</div>
				<?php if($categories_enabled) { ?>
				<div class="ps-pages__tabs">
					<div class="ps-pages__tabs-inner">
						<div class="ps-pages__tab <?php if(!$categories_tab) echo "ps-pages__tab--active";?>"><a href="<?php echo PeepSo::get_page('pages').'?category=0';?>"><?php echo __('Pages', 'pageso'); ?></a></div>
						<div class="ps-pages__tab <?php if($categories_tab) echo "ps-pages__tab--active";?>"><a href="<?php echo PeepSo::get_page('pages').'?category=1';?>"><?php echo __('Page Categories', 'pageso'); ?></a></div>
					</div>
				</div>
				<?php } ?>

				<?php if($categories_tab) { ?>
					<?php $single_column = PeepSo::get_option( 'pages_single_column', 0 ); ?>
					<div class="mb-20"></div>
					<div class="ps-pages__categories ps-js-page-cats" data-mode="<?php echo $single_column ? 'list' : 'grid' ?>"></div>
					<div class="ps-pages__loading ps-js-page-cats-triggerscroll">
						<img class="ps-loading post-ajax-loader ps-js-page-cats-loading" src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>" alt="<?php echo __('Loading', 'pageso'); ?>" />
					</div>
				<?php } else { ?>
					<?php $single_column = PeepSo::get_option( 'pages_single_column', 0 ); ?>
					<div class="mb-20"></div>
					<div class="ps-pages__list <?php echo $single_column ? 'ps-pages__list--single' : '' ?> ps-js-pages" data-mode="<?php echo $single_column ? 'list' : 'grid' ?>"></div>
					<div class="ps-pages__loading ps-js-pages-triggerscroll">
						<img class="ps-loading post-ajax-loader ps-js-pages-loading" src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>" alt="<?php echo __('Loading', 'pageso'); ?>" />
					</div>
				<?php } ?>
			<?php } ?>
		</div>
	</div>
</div><!-- .peepso wrapper -->

<?php

if(get_current_user_id()) {
	PeepSoTemplate::exec_template('activity', 'dialogs');
}
