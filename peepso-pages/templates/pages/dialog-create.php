<div class="ps-page__create">
	<div class="ps-form ps-form--vertical ps-form--page-create">
		<div class="ps-form__row">
			<label class="ps-form__label"><?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjUDMzNXdIYzBoR3lkQzFSQnluZXVPanhyMHRIdU5oVnVXci9TanRXeGRDMkJNNlVHZEFpTmxFbVcyWnYySXZ6S0dGcHRKVFp2N1NNTDRrd05wQklIVjZHdERDc1ArTGhrblcxMG16Mkd1MmFqU2pkTEJxSkQxcTIyakphd1ZMYTh3WWxFeDBJVEhlUVlKR1NHb204cENoTk91WkRiTGpRQnZOaGQ5TjdxY0Zi*/ echo __('Name', 'pageso'); ?> <span class="ps-text--danger">*</span></label>
			<div class="ps-form__field ps-form__field--limit">
				<div class="ps-input__wrapper">
					<input type="text" name="page_name" class="ps-input ps-input--sm ps-input--count ps-js-name-input" value=""
						placeholder="<?php echo __("Enter your page's name...", 'pageso'); ?>" data-maxlength="<?php echo PeepSoPage::$validation['name']['maxlength']; ?>" />
					<div class="ps-form__chars-count"><span class="ps-js-limit ps-tip ps-tip--inline"><?php echo PeepSoPage::$validation['name']['maxlength']; ?></span></div>
				</div>
				<div class="ps-form__field-desc ps-form__required ps-js-error-name" style="display:none"></div>
			</div>
		</div>

		<div class="ps-form__row">
			<label class="ps-form__label"><?php echo __('Description', 'pageso'); ?> <span class="ps-text--danger">*</span></label>
			<div class="ps-form__field ps-form__field--limit">
				<div class="ps-input__wrapper">
					<textarea name="page_desc" class="ps-input ps-input--sm ps-input--textarea ps-input--count ps-js-desc-input"
						placeholder="<?php echo __("Enter your page's description...", 'pageso'); ?>" data-maxlength="<?php echo PeepSoPage::$validation['description']['maxlength']; ?>"></textarea>
					<div class="ps-form__chars-count"><span class="ps-js-limit ps-tip ps-tip--inline" aria-label="<?php echo __('Characters left', 'pageso'); ?>"><?php echo PeepSoPage::$validation['description']['maxlength']; ?></span></div>
				</div>
				<div class="ps-form__field-desc ps-form__required ps-js-error-desc" style="display:none"></div>
			</div>
		</div>

		<?php do_action('peepso_action_render_page_create_form_before'); ?>

		<?php

		if (PeepSo::get_option('pages_categories_enabled', FALSE)) {

			$multiple_enabled = (PeepSo::get_option_new('pages_categories_multiple_max') > 1);
			$input_type = ($multiple_enabled) ?  'checkbox' : 'radio';

			$PeepSoPageCategories = new PeepSoPageCategories(FALSE, TRUE);
			$categories = $PeepSoPageCategories->categories;

			if (count($categories)) {

		?>
				<div class="ps-form__row">
					<label class="ps-form__label"><?php echo __('Category', 'pageso'); ?> <span class="ps-text--danger">*</span></label>
					<div class="ps-form__field">
						<div class="ps-checkbox__grid">
							<?php
							foreach ($categories as $id => $category) {
								echo sprintf('<div class="ps-checkbox"><input type="%s" id="category_' . $id . '" name="category_id" value="%d" class="ps-checkbox__input"><label class="ps-checkbox__label" for="category_' . $id . '">%s</label></div>', $input_type, $id, $category->name);
							}
							?>
						</div>
						<div class="ps-form__field-desc ps-form__required ps-js-error-category_id" style="display:none"></div>
					</div>
				</div>
		<?php

			}
		} // pages_categories_enabled

		?>

		<?php do_action('peepso_action_render_page_create_form_after'); ?>

		<?php
		$privacySettings = PeepSoPagePrivacy::_();
		$privacyDefaultSetting = PeepSoPagePrivacy::_default();
		$privacyDefaultValue = $privacyDefaultSetting['id'];

		?>
		<input type="hidden" name="page_privacy" value="<?php echo $privacyDefaultValue; ?>" />

	</div>
</div>

<?php

// Additional popup options (optional).
$opts = array(
	'title' => __('Create Page', 'pageso'),
	'actions' => array(
		array(
			'label' => __('Cancel', 'pageso'),
			'class' => 'ps-js-cancel'
		),
		array(
			'label' => __('Create Page', 'pageso'),
			'class' => 'ps-js-submit',
			'loading' => true,
			'primary' => true
		)
	)
);

?>
<script type="text/template" data-name="opts"><?php echo json_encode($opts); ?></script>