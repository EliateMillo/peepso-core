<?php if(get_current_user_id() && PeepSo::get_option('wsi_enable_members', 0) && class_exists('Wsi_Public') && method_exists('Wsi_Public', 'widget')) { ?>

<div class="ps-wsi clear">
    <div class="ps-wsi__title"><?php echo esc_attr__('Invite your friends!','peepso-core');?></div>
    <?php echo Wsi_public::widget('');?>
</div>

<?php } ?>
