<div class="peepso">
	<div class="ps-page ps-page--page-members">
		<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjUDMzNXdIYzBoR3lkQzFSQnluZXVPaVJMY0MwQUhWd3VBd1gveU90cWc3d3U0b1dTaHJKemEzeTZZZXdPbjNYOWJNaVVLTEdxK05yRldOWjRTMFRwTmpuTXlHVWQybGtmR0FjNFphT0V2ZjZDUjhwRDdqRVVDVVg5a2VJS3VNS1F0b0FaSzlSaUdhMzRnaS9RaGY0a0xOWCtxdFVLWlFJV050V0tCWG9iY2tv*/ PeepSoTemplate::exec_template('general','navbar'); ?>
		<?php PeepSoTemplate::exec_template('general', 'register-panel'); ?>

		<?php if(get_current_user_id()) { ?>
		<div class="ps-pages">
			<?php PeepSoTemplate::exec_template('pages', 'page-header', array('page'=>$page, 'page_segment'=>$page_segment)); ?>

			<?php
	      $PeepSoPageUser = new PeepSoPageUser($page->id, get_current_user_id());
	      if ($PeepSoPageUser->can('manage_users')) {
	        PeepSoTemplate::exec_template('pages', 'page-members-tabs', array('tab' => FALSE, 'PeepSoPageUser' => $PeepSoPageUser, 'page' => $page,'tab'=>'banned'));
	      	PeepSoTemplate::exec_template('pages', 'page-members-search-form', array());
	      }
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
