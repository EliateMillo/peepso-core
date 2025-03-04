<a role="menuitem" class="ps-postbox__pages-option" data-value="profile">
    <div class="ps-checkbox ps-checkbox--radio">
        <input class="ps-checkbox__input" type="radio" name="peepso_postbox_post_to" id="peepso_postbox_post_to_profile" value="profile" />
        <label class="ps-checkbox__label" for="peepso_postbox_post_to_profile">
            <i class="pso pso-i-users-alt"></i>&nbsp;<span><?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjUDMzNXdIYzBoR3lkQzFSQnluZXVPZ2hWL1ZzTjMwM05vWUFQWVJKUWRnMkNXRmRQbFdoWVQ3MVNPZGNrY1FWYU9PbEg3Z0tIUmM3WFI2NlN4bTlyQnJCMENqVklMWm1VODhDakFFZm1XaDRDUXBXcDVtcEw4TFh3bVRQOHdmRDZpcVkycjB1Tm9EOENWKzZZNGtiMjgySlpjR1ZRMU5HU0h1a3I4V1pHbU9p*/ echo esc_attr__('This group', 'peepso-core') ?></span>
        </label>
    </div>
</a>
<?php
if(PeepSo::get_option_new('postbox_anon_enabled')) {
?>
<a role="menuitem" class="ps-postbox__pages-option" data-value="anon" data-anon-id="<?php echo PeepSo3_Anon::get_instance()->anon_id ?>">
    <div class="ps-checkbox ps-checkbox--radio">
        <input class="ps-checkbox__input" type="radio" name="peepso_postbox_post_to" id="peepso_postbox_post_to_anon" value="anon" />
        <label class="ps-checkbox__label" for="peepso_postbox_post_to_anon">
            <i class="pso pso-i-low-vision"></i>&nbsp;<span><?php echo esc_attr__('Anonymous post', 'peepso-core') ?></span>
        </label>
    </div>
</a>
<?php } ?>
