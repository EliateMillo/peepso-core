<?php

    $profile_url = PeepSoUser::get_instance(get_current_user_id())->get_profileurl();
    $notif_url = $profile_url . 'about/notifications/';

?><div class="ps-notif__box-title"><?php echo esc_attr__('Notifications', 'peepso-core'); ?></div>
<div class="ps-notif__box-actions">
	<a href="<?php echo $notif_url; ?>"><?php echo esc_attr__('Settings', 'peepso-core'); ?></a>
</div>