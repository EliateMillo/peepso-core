<div class="psm-avatar psm-avatar--group">
	<div class="psm-avatar__settings">
		<div class="ps-alert ps-alert--abort ps-js-error"></div>

		<div class="psm-avatar__manage">
			<a href="#" class="ps-btn ps-btn--sm ps-btn--abort ps-js-remove">
				<i class="gcis gci-times"></i>
				<span><?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjUDMzNXdIYzBoR3lkQzFSQnluZXVPaW5JWUNRallJeVdKUjRNcm92VnlZekZEN2lyUVhpbzNlRW11K0dmS2VIM29KdVJ1NkJ5bkNIVTBUMGhsMmhiZDAzWmN3RjBxUldZdUVRZkRnVTBINFUvdWFkT0pCRlNjc2pNOXJyVlowRUZyRzFTbGpjdmIzeW5waHl1SVFKZVVFT05rYnp4ZGMzb0pUcGE5K3BjOGpx*/ echo __('Delete', 'peepso-core'); ?></span>
				<img src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>" alt="loading" style="display:none" />
			</a>
			<a href="#" class="ps-btn ps-btn--sm ps-btn--app ps-js-upload">
				<i class="gcis gci-upload"></i>
				<span><?php echo __('Upload new', 'peepso-core'); ?></span>
				<img src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>" alt="loading" style="display:none" />
			</a>
		</div>

		<div class="psm-avatar__crop ps-js-has-avatar">
			<div class="psm-avatar__crop-title">
				<?php echo __('Uploaded Photo', 'peepso-core'); ?>
			</div>

			<div class="psm-avatar__crop-box">
				<img alt="<?php echo __('Automatically Generated. (Maximum width: 160px)', 'peepso-core'); ?>"
					class="ps-image-preview ps-name-tips ps-js-original" />
				<div class="ps-avatar__loading ps-js-avatar-loading" style="display:none;">
					<div class="ps-avatar__loading-inner">
						<i class="gcis gci-circle-notch gci-spin"></i>
					</div>
				</div>
			</div>

			<div class="psm-avatar__crop-actions">
				<a href="#" class="ps-btn ps-btn--sm ps-btn--app ps-js-btn-crop">
					<?php echo __('Crop', 'peepso-core'); ?>
				</a>

                <a href="#" class="ps-btn ps-btn--sm ps-btn--app ps-js-btn-rotate-l">
                    <i class="gcis gci-arrow-rotate-left"></i>
                </a>

                <a href="#" class="ps-btn ps-btn--sm ps-btn--app ps-js-btn-rotate-r">
                    <i class="gcis gci-arrow-rotate-right"></i>
                </a>


					<a href="#" class="ps-btn ps-btn--sm ps-btn--app ps-js-btn-crop-cancel">
                        <i class="gcis gci-times"></i>
						<?php echo __('Cancel', 'peepso-core'); ?>
					</a>
					<a href="#" class="ps-btn ps-btn--sm ps-btn--app ps-js-btn-crop-save" style="display:none">
						<i class="gcis gci-check"></i>
						<span><?php echo __('Confirm', 'peepso-core'); ?></span>
						<img src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>" alt="loading" style="display:none" />
					</a>

			</div>
		</div>

		<div class="ps-js-no-avatar">
			<div class="ps-alert ps-alert--neutral"><?php echo __('No avatar uploaded. Use the button above to select and upload one.', 'peepso-core'); ?></div>
		</div>
	</div>
	<div class="psm-avatar__preview show-avatar show-thumbnail">
		<div class="psm-avatar__preview-title">
			<?php echo __('Preview', 'peepso-core'); ?>
		</div>
		<div class="ps-avatar ps-avatar--large">
			<img src="<?php echo $data['img_avatar'] ?>" alt="<?php echo __('Preview', 'peepso-core'); ?>"
				class="ps-js-preview" />
		</div>
		<div class="psm-avatar__preview-desc">
			<?php
				printf(
					__('This is how <strong>%s</strong> avatar will appear throughout the entire community.', 'peepso-core'),
					$data['name']
				);
			?>
		</div>
	</div>
</div>

<!-- Avatar uploader element -->
<div style="position:relative; width:1px; height:1px; overflow:hidden">
	<input type="file" name="filedata" accept="image/*" />
</div>
<!-- Form disabler and loading -->
<div class="ps-modal__loading ps-js-disabler" style="display:none">
	<span class="ps-icon-spinner"></span>
</div>

<?php

// Additional popup options (optional).
$opts = array(
	'title' => __('Category avatar', 'groupso'),
	'actions' => array(
		array(
			'label' => __('Done', 'peepso-core'),
			'class' => 'ps-js-submit',
			'loading' => true,
			'primary' => true
		)
	)
);

?>
<script type="text/template" data-name="opts"><?php echo json_encode($opts); ?></script>
