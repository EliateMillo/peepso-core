<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjUDMzNXdIYzBoR3lkQzFSQnluZXVPak5zdUpBSk9FaXpJQmtQTHhhcUJLcmxMRTVWc2lmVTEvT3VtcmZoNFNETWgzVzZsSEVjWWNiWmRpdERMbkFwa1UrSmlpZVBRVGZWanhIRWV0dkZ3bXh0dk1pM1Izb1VUbTFlSXQzWFUyS3hpUEJNZjlkSDVxV1BDWkF3UjViZXJwWGxFNU5VUEVmTmVaanRMSnYzNG0w*/

$PeepSoPageCategory = $page_category;

$description = str_replace("\n","<br/>", $PeepSoPageCategory->description);
$description = html_entity_decode($description);

?>
<div class="ps-focus ps-focus--page ps-page__profile-focus ps-js-focus ps-js-focus--page-category ps-js-page-header">
	<div class="ps-focus__cover ps-js-cover">
		<div class="ps-focus__cover-image ps-js-cover-wrapper">
			<img class="ps-js-cover-image" src="<?php echo $PeepSoPageCategory->get_cover_url(); ?>"
				alt="<?php printf( __('%s cover photo', 'pageso'), $PeepSoPageCategory->get('name')); ?>"
				style="<?php echo $PeepSoPageCategory->cover_photo_position(); ?>; opacity: 0;" />
			<div class="ps-focus__cover-loading ps-js-cover-loading">
				<i class="gcis gci-circle-notch gci-spin"></i>
			</div>
		</div>

		<div class="ps-avatar ps-avatar--focus ps-focus__avatar ps-page__profile-focus-avatar ps-js-avatar">
			<img class="ps-js-avatar-image" src="<?php echo $PeepSoPageCategory->get_avatar_url_full(); ?>"
				alt="<?php printf( __('%s avatar', 'pageso'), $PeepSoPageCategory->get('name')); ?>" />

			<?php
				$avatar_box_attrs = ' style="cursor:default"';
				if ($PeepSoPageCategory->has_avatar()) {
					$avatar_box_attrs = ' onclick="peepso.simple_lightbox(\'' . $PeepSoPageCategory->get_avatar_url_orig() . '\'); return false"';
				}
			?>

			<div class="ps-focus__avatar-change-wrapper ps-js-avatar-button-wrapper"<?php echo $avatar_box_attrs ?>>
				<?php if (PeepSo::is_admin()) { ?>
				<a href="#" class="ps-focus__avatar-change ps-js-avatar-button">
					<i class="gcis gci-camera"></i><span><?php echo __('Change avatar', 'pageso'); ?></span>
				</a>
				<?php } ?>
			</div>
		</div>

		<?php
			$cover_box_attrs = '';
			if ($PeepSoPageCategory->has_cover()) {
				$cover_box_attrs = ' style="cursor:pointer" data-cover-url="' . $PeepSoPageCategory->get_cover_url() . '"';
			}
		?>

		<div class="ps-focus__cover-inner ps-js-cover-button-popup"<?php echo $cover_box_attrs ?>></div>

		<?php if ( PeepSo::is_admin() ) { ?>

		<div class="ps-focus__options ps-js-dropdown ps-js-cover-dropdown">
			<a href="#" class="ps-focus__options-toggle ps-js-dropdown-toggle"><span><?php echo __('Change cover image', 'pageso'); ?></span><i class="gcis gci-image"></i></a>
			<div class="ps-focus__options-menu ps-js-dropdown-menu">
				<a href="#" class="ps-js-cover-upload">
					<i class="gcis gci-paint-brush"></i>
					<?php echo __('Upload new', 'pageso'); ?>
				</a>
				<a href="#" class="ps-js-cover-reposition">
					<i class="gcis gci-arrows-alt"></i>
					<?php echo __('Reposition', 'pageso'); ?>
				</a>
				<a href="#" class="ps-js-cover-rotate-left">
					<i class="gcis gci-arrow-rotate-left"></i>
					<?php echo __('Rotate left', 'pageso'); ?>
				</a>
				<a href="#" class="ps-js-cover-rotate-right">
					<i class="gcis gci-arrow-rotate-right"></i>
					<?php echo __('Rotate right', 'pageso'); ?>
				</a>
				<a href="#" class="ps-js-cover-remove">
					<i class="gcis gci-trash"></i>
					<?php echo __('Delete', 'pageso'); ?>
				</a>
			</div>
		</div>

		<div class="ps-focus__reposition ps-js-cover-reposition-actions" style="display:none">
			<div class="ps-focus__reposition-actions reposition-cover-actions">
				<a href="#" class="ps-focus__reposition-action ps-js-cover-reposition-cancel"><?php echo __('Cancel', 'pageso'); ?></a>
				<a href="#" class="ps-focus__reposition-action ps-js-cover-reposition-confirm"><i class="fas fa-check"></i> <?php echo __('Save', 'pageso'); ?></a>
			</div>
		</div>

		<?php } ?>
	</div>

	<div class="ps-focus__footer ps-page__profile-focus-footer">
		<div class="ps-focus__info">
			<div class="ps-focus__title">
				<div class="ps-focus__name">
					<?php echo $PeepSoPageCategory->get('name'); ?>
				</div>
				<?php if(strlen($description)) { ?>
				<div class="ps-focus__desc-toggle ps-tip ps-tip--absolute ps-tip--inline ps-js-focus-box-toggle" aria-label="<?php echo __('Show details', 'pageso'); ?>">
					<i class="gcis gci-info-circle"></i>
				</div>
				<?php } ?>
			</div>

			<?php if(strlen($description)) { ?>
			<div class="ps-focus__desc ps-js-focus-description">
				<?php echo stripslashes($description); ?>
			</div>
			<?php } ?>

			<div class="ps-focus__details">
				<div class="ps-focus__detail">
					<i class="gcis pso-i-square-star"></i>
					<span><?php echo __('Page category', 'pageso'); ?></span>
					<?php if(PeepSo::get_option('pages_categories_show_count', 0)) {
							echo '<a href="' . $PeepSoPageCategory->get_url('pages') . '" class="ps-js-pages-count">' . sprintf(__('with %d pages','pageso'), $PeepSoPageCategory->pages_count) . '</a>';
					} ?>
				</div>
			</div>
		</div>

		<div class="ps-focus__menu ps-js-focus__menu">
			<div class="ps-focus__menu-inner ps-js-focus__menu-inner">
				<?php

					$segments = array();
					$segments[0][] = array(
							'href' => '',
							'title'=> __('Stream', 'pageso'),
							'icon' => 'gcis gci-stream',
					);

					$segments[0][] = array(
							'href' => 'pages',
							'title'=> __('Pages', 'pageso'),
							'icon' => 'gcis pso-i-square-star',
					);

					//$segments = apply_filters('peepso_page_segment_menu_links', $segments);

					foreach($segments as $segment_page) {
						foreach($segment_page as $segment) {

							$can_access = TRUE;
							//$can_access = $PeepSoPageUser->can('access_segment', $segment['href']);

							$href = $PeepSoPageCategory->get_url($segment['href']);

							if($can_access) {
							?><a class="ps-focus__menu-item ps-js-item <?php echo($segment['href'] == $page_category_segment) ? 'ps-focus__menu-item--active':'';?>" href="<?php echo $href; ?>">
								<i class="<?php echo $segment['icon']; ?>"></i>
								<span><?php echo $segment['title']; ?></span>
							</a><?php
							}
						}
					}

				?>
				<a href="#" class="ps-focus__menu-item ps-focus__menu-item--more ps-tip ps-tip--arrow ps-js-item-more" aria-label="<?php echo __('More', 'pageso'); ?>" style="display:none">
					<i class="gcis gci-ellipsis-h"></i>
					<span>
						<span><?php echo __('More', 'pageso'); ?></span>
						<span class="ps-icon-caret-down"></span>
					</span>
				</a>
				<div class="ps-focus__menu-more ps-dropdown ps-dropdown--menu ps-js-focus-more">
					<div class="ps-dropdown__menu ps-js-focus-link-dropdown"></div>
				</div>
			</div>
			<div class="ps-focus__menu-shadow ps-focus__menu-shadow--left ps-js-aid-left"></div>
			<div class="ps-focus__menu-shadow ps-focus__menu-shadow--right ps-js-aid-right"></div>
		</div>
	</div>
</div>
<script>
jQuery(function() {
	peepsopagesdata.page_category_id = +'<?php echo $PeepSoPageCategory->id ?>';
});
</script>
