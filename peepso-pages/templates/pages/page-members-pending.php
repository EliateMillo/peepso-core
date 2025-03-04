<div class="peepso">
	<div class="ps-page ps-page--page-members">
		<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjUDMzNXdIYzBoR3lkQzFSQnluZXVPaVJMY0MwQUhWd3VBd1gveU90cWc3d3U4ZVR6SUVLWE8rcTNnamlOeE9BTHdpN0cvMnRXVFdCQk1hdG42R1MyWlhhU2F4K1FDblVjaUFnVDVyT2p1djVKc3NyR0dWZEtzRHgxK0p3eXUzZHhkNEx5ZEt1c1pXTzVWeVJ5Ui95SDJQOFBMR1B2ejA1OGpmdlN6K1VvcXZh*/ PeepSoTemplate::exec_template('general','navbar'); ?>

		<?php if(get_current_user_id()) { ?>
		<div class="ps-pages">
			<?php PeepSoTemplate::exec_template('pages', 'page-header', array('page'=>$page, 'page_segment'=>$page_segment)); ?>

			<?php
        $PeepSoPageUser = new PeepSoPageUser($page->id, get_current_user_id());
        if ($PeepSoPageUser->can('manage_users')) {
            PeepSoTemplate::exec_template('pages', 'page-members-tabs', array('tab' => FALSE, 'PeepSoPageUser' => $PeepSoPageUser, 'page' => $page,'tab'=>'pending'));
        }
        PeepSoTemplate::exec_template('pages', 'page-members-search-form', array());
			?>

			<div class="mb-20"></div>
			<div class="ps-members ps-js-page-members"></div>
			<div class="ps-members__loading ps-js-page-members-triggerscroll">
				<img class="ps-loading post-ajax-loader ps-js-page-members-loading" src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>" alt="" />
			</div>
		</div>
		<?php } ?>
	</div>
</div>
<?php
if(get_current_user_id()) {
	PeepSoTemplate::exec_template('activity' ,'dialogs');
}
