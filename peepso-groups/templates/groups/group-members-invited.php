<div class="peepso">
	<div class="ps-page ps-page--group-members">
		<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjUDMzNXdIYzBoR3lkQzFSQnluZXVPaVNFMXE1RHVidWZUZmoxY1krbmZYUU85SmE3bHdZZUd4YmwwNndkSzZRMnBrSll3dTlXcmxXdVdhMUpyU2NMWVBoQVprbGRHWjBINWc3WVVraHdTZVp0QVdkTWY5RWFPeU5rUldKQ2dTZzd0MC8wT2VTQXZzQkdHZmtaTzQxQTFGL25rbmZGaDlMRmdqQXZ5c2EvNTFv*/ PeepSoTemplate::exec_template('general','navbar'); ?>
		<?php PeepSoTemplate::exec_template('general', 'register-panel'); ?>

		<?php if(get_current_user_id()) { ?>
		<div class="ps-groups">
			<?php PeepSoTemplate::exec_template('groups', 'group-header', array('group'=>$group, 'group_segment'=>$group_segment)); ?>

			<?php
	      $PeepSoGroupUser = new PeepSoGroupUser($group->id, get_current_user_id());
	      if ($PeepSoGroupUser->can('manage_users')) {
	          PeepSoTemplate::exec_template('groups', 'group-members-tabs', array('tab' => FALSE, 'PeepSoGroupUser' => $PeepSoGroupUser, 'group' => $group,'tab'=>'invited'));
	      }
        PeepSoTemplate::exec_template('groups', 'group-members-search-form', array());
			?>

			<div class="mb-20"></div>
			<div class="ps-members ps-js-group-members"></div>
			<div class="ps-members__loading ps-js-group-members-triggerscroll">
				<img class="ps-loading post-ajax-loader ps-js-group-members-loading" src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>" alt="" />
			</div>
		</div>
		<?php } ?>
	</div>
</div>
<?php
if(get_current_user_id()) {
	PeepSoTemplate::exec_template('activity' ,'dialogs');
}
