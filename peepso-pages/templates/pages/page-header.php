<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjUDMzNXdIYzBoR3lkQzFSQnluZXVPakJpU0VoQlREbG1qbFQrYm92MnIxNmZtZ0FmamNRV3VWY29PODY4emxCWjNrREI0Um5MSnhzOGZsWktOVjlibkxZU3lZN3YzOUhJVVV4c1dscUtsS2gxUzlSMEN5Z3d6MVVCRHZKcTRvSjhiWkhzN2ZpZ1NvemFoVXdhM2F0N2EwamZ3dHJBb1pLcXZ3WXZCdFFMRGhH*/
$PeepSoPageUser = new PeepSoPageUser($page->id);
#$PeepSoPage = new PeepSoPage($page->id);
$PeepSoPage = $page;
$coverUrl = $PeepSoPage->get_cover_url();
$has_cover = false;

if (FALSE !== stripos($coverUrl, 'peepso/pages/'))
	$has_cover = true;

if (FALSE === $PeepSoPageUser->can('manage_page') || (FALSE === $has_cover)) {
	$reposition_style = 'display:none;';
	$cover_class = 'default';
} else {
	$reposition_style = '';
	$cover_class = 'has-cover';
}

$description = str_replace("\n", "<br/>", $page->description);
$description = html_entity_decode($description);

$page_categories = PeepSoPageCategoriesPages::get_categories_for_page($page->id);
$page_categories_html = array();

?>
<div class="ps-focus ps-focus--page ps-page__profile-focus ps-js-focus ps-js-focus--page ps-js-page-header">
	<div class="ps-focus__cover ps-js-cover">
		<div class="ps-focus__cover-image ps-js-cover-wrapper">
			<img class="ps-js-cover-image" src="<?php echo $PeepSoPage->get_cover_url(); ?>"
				alt="<?php printf(__('%s cover photo', 'pageso'), $PeepSoPage->get('name')); ?>"
				style="<?php echo $PeepSoPage->cover_photo_position(); ?>; opacity: 0;" />
			<div class="ps-focus__cover-loading ps-js-cover-loading">
				<i class="gcis gci-circle-notch gci-spin"></i>
			</div>
		</div>

		<div class="ps-avatar ps-avatar--focus ps-focus__avatar ps-page__profile-focus-avatar ps-js-avatar">
			<img class="ps-js-avatar-image" src="<?php echo $PeepSoPage->get_avatar_url_full(); ?>"
				alt="<?php printf(__('%s avatar', 'pageso'), $PeepSoPage->get('name')); ?>" />

			<?php
			$avatar_box_attrs = ' style="cursor:default"';
			if ($PeepSoPage->has_avatar()) {
				$avatar_box_attrs = ' onclick="peepso.simple_lightbox(\'' . $PeepSoPage->get_avatar_url_orig() . '\'); return false"';
			}
			?>

			<div class="ps-focus__avatar-change-wrapper ps-js-avatar-button-wrapper" <?php echo $avatar_box_attrs ?>>
				<?php if ($PeepSoPageUser->can('manage_page')) { ?>
					<a href="#" class="ps-focus__avatar-change ps-js-avatar-button">
						<i class="gcis gci-camera"></i><span><?php echo __('Change avatar', 'pageso'); ?></span>
					</a>
				<?php } ?>
			</div>
		</div>

		<?php
		$cover_box_attrs = '';
		if ($PeepSoPage->has_cover()) {
			$cover_box_attrs = ' style="cursor:pointer" data-cover-url="' . $PeepSoPage->get_cover_url() . '"';
		}
		?>

		<div class="ps-focus__cover-inner ps-js-cover-button-popup" <?php echo $cover_box_attrs ?>>
			<div class="ps-focus__cover-actions ps-js-page-header-actions ps-js-loading">
				<button class="ps-focus__cover-action">
					<img src="<?php echo PeepSo::get_asset('images/ajax-loader.gif') ?>" />
				</button>
			</div>
		</div>

		<?php if ($PeepSoPageUser->can('manage_page')) { ?>

			<div class="ps-focus__options ps-js-dropdown ps-js-cover-dropdown">
				<a href="#" class="ps-focus__options-toggle ps-js-dropdown-toggle"><span><?php echo __('Change cover image', 'pageso'); ?></span><i class="gcis gci-image"></i></a>
				<div class="ps-focus__options-menu ps-js-dropdown-menu">
					<a href="#" class="ps-js-cover-upload">
						<i class="gcis gci-paint-brush"></i>
						<?php echo __('Upload', 'pageso'); ?>
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
					<?php echo $page->name; ?>
				</div>
				<div class="ps-focus__desc-toggle ps-tip ps-tip--absolute ps-tip--inline ps-tip--bottom ps-js-focus-box-toggle" aria-label="<?php echo __('Show details', 'pageso'); ?>">
					<i class="gcis gci-info-circle"></i>
				</div>
			</div>

			<div class="ps-focus__desc ps-js-focus-description">
				<!-- Description -->
				<?php

				$description = stripslashes($description);
				if (PeepSo::get_option_new('md_pages_about', 0)) {
					$description = PeepSo::do_parsedown($description);
				}

				echo $description;

				?>

				<!-- Categories -->
				<?php if (PeepSo::get_option('pages_categories_enabled', FALSE)) { ?>
					<div class="ps-focus__desc-details">
						<?php if (count($page_categories) > 1) { ?><i class="gcis gci-tags"></i> <?php echo __('Page categories', 'pageso'); ?>:<?php } else { ?><i class="gcis gci-tag"></i> <?php echo __('Page category', 'pageso'); ?>:<?php } ?>
							<?php

							foreach ($page_categories as $PeepSoPageCategory) {
								echo "<a href=\"{$PeepSoPageCategory->get_url()}\">{$PeepSoPageCategory->name}</a>";
							}

							?>
					</div>
				<?php } ?>
			</div>

			<div class="ps-focus__details">
				<!-- DETAILS -->

				<!-- Members -->
				<a class="ps-focus__detail" href="<?php echo $page->get_url() . 'members/'; ?>">
					<i class="gcis gci-user-friends"></i>
					<span class="ps-js-member-count"><?php printf(_n('%s follower', '%s followers', $page->members_count, 'pageso'), number_format_i18n($page->members_count)); ?></span>
				</a>

			</div>
			<div class="ps-focus__mobile-actions ps-js-page-header-actions ps-js-loading">
				<button class="ps-focus__cover-action">
					<img src="<?php echo PeepSo::get_asset('images/ajax-loader.gif') ?>" />
				</button>
			</div>
		</div>

		<div class="ps-focus__menu ps-js-focus__menu">
			<div class="ps-focus__menu-inner ps-js-focus__menu-inner">
				<?php

				$segments = array();
				$segments[0][] = array(
					'href' => '',
					'title' => __('Stream', 'pageso'),
					'icon' => 'gcis gci-stream',
				);

				if ($PeepSoPageUser->can('manage_page')) {
					$segments[0][] = array(
						'href' => 'settings',
						'title' => __('Settings', 'pageso'),
						'icon' => 'gcis gci-cog',
					);
				}

				$title = __('Followers', 'pageso');

				if ($PeepSoPageUser->can('manage_users') && $pending = $page->pending_admin_members_count) {
					$title .= ' <span class="ps-js-pending-label">(' . sprintf(__('<span class="ps-js-pending-count" data-id="%d">%s</span> pending', 'pageso'), $page->id, $pending) . ')</span>';
				}

				if ($PeepSoPageUser->can('view_users')) {
					$segments[0][] = array(
						'href' => 'members',
						'title' => $title,
						'icon' => 'gcis gci-user-friends',
					);
				}

				$segments['_PeepSoPage'] = $PeepSoPage;
				$segments['_PeepSoPageUser'] = $PeepSoPageUser;

				$segments = apply_filters('peepso_page_segment_menu_links', $segments);

				unset($segments['_PeepSoPage']);
				unset($segments['_PeepSoPageUser']);

				foreach ($segments as $segment_page) {
					foreach ($segment_page as $segment) {

						$can_access = $PeepSoPageUser->can('access_segment', $segment['href']);

						$href = $page->get_url();

						if (strlen($segment['href'])) {
							$href .= $segment['href'] . '/';

							// If passing an external link, treat it as such
							if ('http' == substr($segment['href'], 0, 4)) {
								$href = $segment['href'];
							}
						}

						if ($can_access) {
				?>
							<a class="ps-focus__menu-item ps-js-item <?php echo ($segment['href'] == $page_segment) ? 'ps-focus__menu-item--active' : ''; ?>" href="<?php echo $href; ?>">
								<i class="<?php echo $segment['icon']; ?>"></i>
								<span><?php echo $segment['title']; ?></span>
							</a>
				<?php
						}
					}
				}

				?>
				<a href="#" class="ps-focus__menu-item ps-focus__menu-item--more ps-tip ps-tip--arrow ps-js-item-more" aria-label="<?php echo __('More', 'pageso'); ?>" style="display:none">
					<i class="gcis gci-ellipsis-h"></i>
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
		peepsopagesdata.page_id = +'<?php echo $page->id ?>';
	});
</script>