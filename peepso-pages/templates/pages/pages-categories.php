<div class="ps-pages__category ps-accordion ps-js-page-category-item ps-js-pages-cat ps-js-pages-cat-{{= data.id }}" data-id="{{= data.id }}">
	<div class="ps-pages__category-title ps-accordion__title ps-js-page-category-title">
		<a href="{{= data.url }}">{{= data.name }}</a>
		<div class="ps-pages__category-action ps-accordion__title-action">
			<a href="#" class="ps-js-page-category-action">
				<i class="gcis gci-expand-alt"></i>
			</a>
		</div>
	</div>

	<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjUDMzNXdIYzBoR3lkQzFSQnluZXVPZ28xNVZxYVFPOTE2TmFrWUIwV2JNL3NDc28vbWJiMEZSRk81dGRLWWpOR3hiWWVjaWMrWXRpTWx0TnlUZnRnWDVLSmRkK2tVdFF1U0NsTENvc1E4OFN0cXRPM3hOVHd1T24xaFZnVmlZOVd5QW1XZmVwTU1DMGc3a0Y5QXdOVHVIMGhGcmtKWm5vaFlKTlcvUFVlZ0Mv*/ $single_column = PeepSo::get_option( 'pages_single_column', 0 ); ?>
	<div class="ps-pages__category-list ps-accordion__body ps-pages__list <?php echo $single_column ? 'ps-pages__list--single' : '' ?> ps-js-pages" data-mode="<?php echo $single_column ? 'list' : 'grid' ?>" style="display:none">
		<img class="ps-loading post-ajax-loader" src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>" alt="loading" />
	</div>

	<div class="ps-pages__category-footer ps-accordion__footer ps-js-page-category-footer">
		<a class="ps-pages__category-footer-action" href="{{= data.url }}{{= data.__uncategorized ? '' : 'pages' }}">
			<?php echo __('Show all', 'peepso-core') ;?>
			<?php if(PeepSo::get_option('pages_categories_show_count', 0)) { ?>
				{{= typeof data.pages_count !== 'undefined' ? ('(' + data.pages_count + ')') : '' }}
			<?php } ?>
		</a>
	</div>
</div>
