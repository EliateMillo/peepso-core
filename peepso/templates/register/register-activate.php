<div class="peepso">
	<div class="ps-page ps-page--register ps-page--register-activate">
		<h2><?php echo esc_attr__('Account Activation', 'peepso-core'); ?></h2>
		<p><?php echo esc_attr__('Please enter your activation code below to enable your account.', 'peepso-core'); ?></p>
		<?php
		if (isset($error)) {
			PeepSoGeneral::get_instance()->show_error($error);
		}
		?>
		<div class="psf-register psf-register--activate">
			<form class="ps-form ps-form--register ps-form--register-activate" name="resend-activation" action="<?php PeepSo::get_page('register'); ?>?activate" method="post">
				<div class="ps-form__grid">
					<div class="ps-form__row">
						<label for="activation" class="ps-form__label"><?php echo esc_attr__('Activation Code:', 'peepso-core'); ?>
							<span class="ps-form__required">&nbsp;*<span></span></span>
						</label>
						<div class="ps-form__field">
							<?php
								$input = new PeepSoInput();
								$value = $input->value('community_activation_code', $input->value('peepso_activation_code', '', FALSE), FALSE); // Fallback activation code - see #3142
							?>
							<input type="text" name="community_activation_code" class="ps-input" value="<?php echo $value; ?>" placeholder="<?php echo esc_attr__('Activation code', 'peepso-core'); ?>" />
						</div>
					</div>
					<div class="ps-form__row submitel">
						<?php $prefUrl = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : PeepSo::get_page('activity'); ?>
						<a class="ps-btn" href="<?php echo $prefUrl; ?>"><?php echo esc_attr__('Back', 'peepso-core'); ?></a>
						<input type="submit" name="submit-activate" class="ps-btn ps-btn--action" value="<?php echo esc_attr__('Submit', 'peepso-core'); ?>" />
					</div>
				</div>
			</form>
		</div>
	</div>
</div>
