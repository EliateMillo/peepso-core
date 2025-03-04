<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjUDMzNXdIYzBoR3lkQzFSQnluZXVPaVJMY0MwQUhWd3VBd1gveU90cWc3d0crZGFNTm1FRHlaRWk4YWJOUWVURG0zWjZvYklodkNKQmY2Qkp0Rzd4aElodVZNT2cwa0xKdGhXcmMrOWdLQ1BQK2xGNnVjd0JGckFxYWJEa0Y4dEFZUGVHeVF1TzhRb2xrSnhiSlFXRXUxcWdSU0RoY0tEQzhqSldaWG05UnlH*/ $PeepSoPageUser = new PeepSoPageUser($page->id); ?>
<div class="peepso">
	<div class="ps-page ps-page--page-members">
		<?php PeepSoTemplate::exec_template('general','navbar'); ?>

		<?php if($PeepSoPageUser->can('access')) { ?>
		<div class="ps-pages">
			<?php PeepSoTemplate::exec_template('pages', 'page-header', array('page'=>$page, 'page_segment'=>$page_segment)); ?>

			<?php if (! get_current_user_id()) { PeepSoTemplate::exec_template('general','login-profile-tab'); } ?>

			<?php
        $PeepSoPageUser = new PeepSoPageUser($page->id, get_current_user_id());
        PeepSoTemplate::exec_template('pages', 'page-members-tabs', array('tab' => 'management', 'PeepSoPageUser' => $PeepSoPageUser, 'page' => $page));
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
