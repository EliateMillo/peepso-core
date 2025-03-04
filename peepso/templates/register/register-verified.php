<div class="peepso">
	<div class="ps-page ps-page--register ps-page--register-verified">
		<h2><?php echo esc_attr__('Email Verified', 'peepso-core'); ?></h2>
		<p><?php echo esc_attr__('Your email has been verified. Until the site administrator approves your account, you will not be able to login. Once your account has been approved, you will receive a notification email.', 'peepso-core'); ?></p>
		<div class="ps-page__footer">
			<?php $prefUrl = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : PeepSo::get_page('activity'); ?>
			<a class="ps-btn" href="<?php echo $prefUrl; ?>"><?php echo esc_attr__('Back', 'peepso-core'); ?></a>
		</div>
	</div>
</div>
