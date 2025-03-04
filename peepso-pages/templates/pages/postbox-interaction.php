<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjUDMzNXdIYzBoR3lkQzFSQnluZXVPaEdIeml4Q0ZDNzFacmxLam9zL0hjNU9ETzRoQkJnVDdhMjI0c0krZkl6T2tNb1ppaERqWUcwMHlZUlpjSFBndk8rRVFLbnJFbGJUVHE4ejZkSmFXUjlJREJJOFRQNlVkZ2FXUDdkVmVpLzdPTUp3cWExQ21qSk9abWdJR0QxWXJVNTBmdmZMWWEvTlY1L0YyaGZoMFRk*/

$random = rand();

?>

<div class="ps-postbox__pages-options ps-dropdown__menu ps-js-postbox-dropdown ps-js-postbox-page" style="display:none">
  <a role="menuitem" class="ps-postbox__pages-option" data-option-value="">
    <div class="ps-checkbox">
      <input class="ps-checkbox__input" type="radio" name="peepso_postbox_page_<?php echo $random ?>"
        id="peepso_postbox_page_<?php echo $random ?>_" value="" <?php if(!$category_id) { echo ' checked="checked" '; } ?> />
      <label class="ps-checkbox__label" for="peepso_postbox_page_<?php echo $random ?>_">
        <span><?php echo __('My profile', 'pageso') ?></span>
      </label>
    </div>
  </a>

  <a role="menuitem" class="ps-postbox__pages-option ps-postbox__pages-option--pages" data-option-value="page">
    <div class="ps-checkbox">
      <input class="ps-checkbox__input" type="radio" name="peepso_postbox_page_<?php echo $random ?>"
        id="peepso_postbox_page_<?php echo $random ?>_page" value="page" <?php if($category_id) { echo ' checked="checked" '; } ?>/>
      <label class="ps-checkbox__label" for="peepso_postbox_page_<?php echo $random ?>_page">
        <span><?php echo __('A page', 'pageso') ?></span>
      </label>
    </div>

    <div class="ps-postbox__pages-search ps-js-page-finder">
      <input type="text" class="ps-input ps-input--sm" name="query" value=""
          placeholder="<?php echo __('Start typing to search...', 'pageso'); ?>" />

      <div class="ps-postbox__pages-view ps-js-result">
        <div class="ps-postbox__pages-list ps-js-result-list" style="max-height:35vh; overflow:auto;"></div>
        <script type="text/template" class="ps-js-result-item-template">
          {{ var __can_pin_posts = data.pageuserajax && data.pageuserajax.can_pin_posts ? 1 : 0 }}
          <div class="ps-postbox__pages-item" data-id="{{= data.id }}" data-name="{{= data.name }}"
              data-can-pin-posts="{{= __can_pin_posts }}">
            <div class="ps-postbox__pages-item-header">
              <div class="ps-postbox__pages-item-name">{{= data.name }}</div>
              {{ if ( data.privacy ) { /**/ }}
              <div class="ps-postbox__pages-item-privacy">
                  <i class="{{= data.privacy.icon }}"></i>
                  {{= data.privacy.name }}
              </div>
              {{ /**/ } }}
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
</div>
