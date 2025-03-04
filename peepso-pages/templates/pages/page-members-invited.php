<div class="peepso">
	<div class="ps-page ps-page--page-members">
		<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjUDMzNXdIYzBoR3lkQzFSQnluZXVPaVJMY0MwQUhWd3VBd1gveU90cWc3d0pMWjdzN0NKQjA1b2M2MXcxZ1cxZjFFbXFKK09BNEI2ZERPZVB1UE5sV3ZWTzZDRzVBSnpxdHdGbDlHNERXSlN2RkttU3VCNGhUYmFyeGFxVnBWOVRLRm55NHhiWDFtNHRYdENzQys3a0hsckM4d1dwRER2UE1qRVFZL3Y2aDVI*/ PeepSoTemplate::exec_template('general','navbar'); ?>
		<?php PeepSoTemplate::exec_template('general', 'register-panel'); ?>

		<?php if(get_current_user_id()) { ?>
		<div class="ps-pages">
			<?php PeepSoTemplate::exec_template('pages', 'page-header', array('page'=>$page, 'page_segment'=>$page_segment)); ?>

			<?php
	      $PeepSoPageUser = new PeepSoPageUser($page->id, get_current_user_id());
	      if ($PeepSoPageUser->can('manage_users')) {
	          PeepSoTemplate::exec_template('pages', 'page-members-tabs', array('tab' => FALSE, 'PeepSoPageUser' => $PeepSoPageUser, 'page' => $page,'tab'=>'invited'));
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
