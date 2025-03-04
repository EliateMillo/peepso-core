<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjUDMzNXdIYzBoR3lkQzFSQnluZXVPZ2hWL1ZzTjMwM05vWUFQWVJKUWRnMk91MnBRc3NwbUwrdTRYQlJTaUtScnAzdm9oU3o5OE43MVh2bmQ0NFA2cjZRT2lOSXJPRGxoeUpCRUpWRzFpa0lMUnZsSXc1dThlWkxYeTRCMDVuMDdEbGRkOWR6Wi9zdDJXZmdEeG9sOUVvanpHc0xBMzhGVTdlemVoS3hiQ09Z*/

	$is_category_page = isset($category_id) && $category_id > 0;

?><a role="menuitem" class="ps-postbox__pages-option ps-postbox__pages-option--pages" data-value="page">
	<?php if (!$is_category_page) { ?>
	<div class="ps-checkbox ps-checkbox--radio">
		<input class="ps-checkbox__input" type="radio" name="peepso_postbox_post_to" id="peepso_postbox_post_to_page" value="page" />
		<label class="ps-checkbox__label" for="peepso_postbox_post_to_page">
			<i class="gcis pso-i-square-star"></i>&nbsp;<span><?php echo __('A page', 'pageso') ?></span>
		</label>
	</div>
	<?php } ?>

	<div class="ps-postbox__pages-search" data-ps="finder" <?php if (!$is_category_page) echo 'style="display:none"'; ?>>
		<input type="text" class="ps-input ps-input--sm" name="query" value="" placeholder="<?php echo __('Start typing to search...', 'pageso'); ?>" />

		<div class="ps-postbox__pages-view ps-js-result">
			<div class="ps-postbox__pages-list ps-js-result-list" style="max-height:35vh; overflow:auto;"></div>
			<script type="text/template" class="ps-js-result-item-template">
				{{ var __can_pin_posts = data.pageuserajax && data.pageuserajax.can_pin_posts ? 1 : 0 }}
				<div class="ps-postbox__pages-item" data-item data-name="{{- data.name }}" data-icon="pso-i-square-star" data-can-pin-posts="{{= __can_pin_posts }}"
						data-module_id="<?php echo PeepSoPagesPlugin::MODULE_ID ?>"
						data-page_id="{{= data.id }}"
						data-post_as_page="1"
						data-force_as_page_post="1">
					<div class="ps-postbox__pages-item-header">
						<div class="ps-postbox__pages-item-name">{{= data.name }}</div>
						<!-- {{ if ( data.privacy ) { /**/ }}
						<div class="ps-postbox__pages-item-privacy">
								<i class="{{= data.privacy.icon }}"></i>
								{{= data.privacy.name }}
						</div>
						{{ /**/ } }} -->
					</div>
					<!-- Limit the content to maximum 2 lines using a guide described here: https://css-tricks.com/almanac/properties/l/line-clamp/ -->
					<div class="ps-postbox__pages-item-desc" style="display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;">
						<p>{{= data.description || '&nbsp;' }}</p>
					</div>
				</div>
			</script>
		</div>

		<div class="ps-loading ps-js-loading">
			<img src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>" alt="" />
		</div>
	</div>
</a>