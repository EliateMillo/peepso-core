<div class="peepso ps-page-profile ps-page--group">
    <?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjUDMzNXdIYzBoR3lkQzFSQnluZXVPajRRdzM0UnZvYS82Wm9OSUdabkVTU3J3bmpNeG1GOHI5MFp5Mi9yMEZQZ0N5QjhWc3d6YTZmamdhVGhpeXNYK05hTkI3SUFpdi9XRTE4eWdUMUJtTjZLK09FMnJFM0d6RGswY0hKVll5TWVYZ0M5dmpvem5FYmdReTY3ZlhwaURINTNCSHY1RTZva3FRa1k5dDFzR1JE*/ PeepSoTemplate::exec_template('general','navbar'); ?>
    <?php //PeepSoTemplate::exec_template('general', 'register-panel'); ?>
    
    <?php $PeepSoPageUser = new PeepSoPageUser($page->id, get_current_user_id());?>
    <?php if($PeepSoPageUser->can('access')) { ?>

    <?php PeepSoTemplate::exec_template('pages', 'page-header', array('page'=>$page, 'page_segment'=>$page_segment)); ?>

    <div class="ps-photos">
        <?php
            if (!get_current_user_id()) {
                PeepSoTemplate::exec_template('general', 'login-profile-tab');
            } 
        ?>

        <div class="ps-photos__header">
            <div class="ps-photos__list-view">
                <div class="ps-btn__group">
                    <a href="#" class="ps-btn ps-btn--sm ps-btn--app ps-btn--cp ps-tip ps-tip--arrow ps-tip--inline ps-js-photos-viewmode" data-mode="small" aria-label="<?php echo __('Small thumbnails', 'picso');?>"><i class="gcis gci-th"></i></a>
                    <a href="#" class="ps-btn ps-btn--sm ps-btn--app ps-btn--cp ps-tip ps-tip--arrow ps-tip--inline ps-js-photos-viewmode" data-mode="large" aria-label="<?php echo __('Large thumbnails', 'picso');?>"><i class="gcis gci-th-large"></i></a>
                </div>
            </div>

            <?php if($PeepSoPageUser->can('post') && apply_filters('peepso_permissions_photos_upload', TRUE)) { ?>
            <div class="ps-photos__actions">
                <a href="#" class="ps-btn ps-btn--sm ps-btn--action" onclick="peepso.photos.show_dialog_album(<?php echo get_current_user_id();?>, this); return false;"><?php echo __('Create Album', 'picso'); ?></a>
            </div>
            <?php } ?>
        </div>

        <div class="ps-tabs ps-photos__tabs ps-tabs--arrows">
            <div class="ps-tabs__item <?php if('latest' === $current) echo 'ps-tabs__item--active' ?>"><a href="<?php echo PeepSoSharePhotos::get_page_url($view_page_id, 'latest'); ?>"><?php echo __('Photos', 'picso'); ?></a></div>
            <div class="ps-tabs__item <?php if('album' === $current) echo 'ps-tabs__item--active' ?>"><a href="<?php echo PeepSoSharePhotos::get_page_url($view_page_id, 'album'); ?>"><?php echo __('Albums', 'picso'); ?></a></div>
        </div>

        <div class="mb-20"></div>

        <div class="ps-page-filters" style="display:none;">
            <select class="ps-select ps-full ps-js-<?php echo $type?>-sortby ps-js-<?php echo $type?>-sortby--<?php echo  apply_filters('peepso_user_profile_id', 0); ?>">
                <option value="desc"><?php echo __('Newest first', 'picso');?></option>
                <option value="asc"><?php echo __('Oldest first', 'picso');?></option>
            </select>
        </div>

        <div class="mb-20"></div>
        <div class="ps-photos__list ps-photos__list--<?php echo $type; ?> ps-js-<?php echo $type; ?> ps-js-<?php echo $type; ?>--<?php echo  apply_filters('peepso_user_profile_id', 0); ?>"></div>
        <div class="ps-scroll ps-js-<?php echo $type?>-triggerscroll ps-js-<?php echo $type?>-triggerscroll--<?php echo  apply_filters('peepso_user_profile_id', 0); ?>">
            <img class="post-ajax-loader ps-js-<?php echo $type?>-loading" src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>" alt="" style="display:none" />
        </div>
        <div class="mb-20"></div>

    </div>
    <?php } ?>

</div><!--end row-->

<?php PeepSoTemplate::exec_template('activity','dialogs'); ?>
