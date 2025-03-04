<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjUDMzNXdIYzBoR3lkQzFSQnluZXVPZ2hWL1ZzTjMwM05vWUFQWVJKUWRnMk91MnBRc3NwbUwrdTRYQlJTaUtScnAzdm9oU3o5OE43MVh2bmQ0NFA2cjZRT2lOSXJPRGxoeUpCRUpWRzFpa0lMUnZsSXc1dThlWkxYeTRCMDVuMDdEbGRkOWR6Wi9zdDJXZmdEeG9sOUVvb2MyNE96ZllOUTB2TWk0QmZtTHRC*/

	$is_category_page = isset($category_id) && $category_id > 0;

?><a role="menuitem" class="ps-postbox__groups-option ps-postbox__groups-option--groups" data-value="group">
    <?php if (!$is_category_page) { ?>
    <div class="ps-checkbox ps-checkbox--radio">
        <input class="ps-checkbox__input" type="radio" name="peepso_postbox_post_to" id="peepso_postbox_post_to_group" value="group" />
        <label class="ps-checkbox__label" for="peepso_postbox_post_to_group">
            <i class="gcis pso-i-users-alt"></i>&nbsp;<span><?php echo __('A group', 'groupso') ?></span>
        </label>
    </div>
    <?php } ?>

	<div class="ps-postbox__groups-search" data-ps="finder" <?php if (!$is_category_page) echo 'style="display:none"'; ?>>
		<input type="text" class="ps-input ps-input--sm" name="query" value="" placeholder="<?php echo __('Start typing to search...', 'groupso'); ?>" />

		<div class="ps-postbox__groups-view ps-js-result">
			<div class="ps-postbox__groups-list ps-js-result-list" style="max-height:35vh; overflow:auto;"></div>
			<script type="text/template" class="ps-js-result-item-template">
				{{ var __can_pin_posts = data.groupuserajax && data.groupuserajax.can_pin_posts ? 1 : 0 }}
				<div class="ps-postbox__groups-item" data-item data-name="{{- data.name }}" data-icon="pso-i-users-alt" data-can-pin-posts="{{= __can_pin_posts }}"
						data-module_id="<?php echo PeepSoGroupsPlugin::MODULE_ID ?>"
						data-group_id="{{= data.id }}"
						data-force_as_group_post="1">
					<div class="ps-postbox__groups-item-header">
						<div class="ps-postbox__groups-item-name">{{= data.name }}</div>
						{{ if ( data.privacy ) { /**/ }}
						<div class="ps-postbox__groups-item-privacy">
								<i class="{{= data.privacy.icon }}"></i>
								{{= data.privacy.name }}
						</div>
						{{ /**/ } }}
					</div>
					<!-- Limit the content to maximum 2 lines using a guide described here: https://css-tricks.com/almanac/properties/l/line-clamp/ -->
					<div class="ps-postbox__groups-item-desc" style="display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;">
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