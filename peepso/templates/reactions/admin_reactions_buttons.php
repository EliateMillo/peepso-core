<div class="ps-settings__bar clearfix">
	<div class="ps-settings__nav">
		<button type="button" class="ps-js-reaction-expand-all">
			<i class="fa fa-expand"></i> <span><?php echo esc_attr__('Expand All', 'peepso-core'); ?></span>
		</button>
		<button type="button" class="ps-js-reaction-collapse-all">
			<i class="fa fa-compress"></i> <span><?php echo esc_attr__('Collapse All', 'peepso-core'); ?></span>
		</button>
	</div>

	<div class="ps-settings__nav ps-settings__nav--right ps-dropdown">
		<button type="button" class="btn-primary ps-js-reaction-new">
			<i class="fa fa-plus"></i> <span><?php echo esc_attr__('Add New', 'peepso-core'); ?></span>
		</button>
	</div>
</div>

<p style="display:none" id="reaction-default-hint"><?php echo esc_attr__('reacted to', 'peepsoreaction');?></p>
