<div class="peepso">
	<div class="ps-page ps-page--group-members">
		<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjUDMzNXdIYzBoR3lkQzFSQnluZXVPaVNFMXE1RHVidWZUZmoxY1krbmZYUUtTaWlTUW93ZWV6ZVBZMFN6QTFTUEdGTnlhUlptWklrVUhKdnFndVl5Y3k5OGw2d3diUWFVbTF5VXNoV0VQNG5PRzhLTGJuNmRrWHdDYnV4ZkZpc2RXUDRSLy92TC9DcDNCaVErazlLcmF2VHN5Z2haRWk2UzlyMTZmM3ZSUmUx*/ PeepSoTemplate::exec_template('general','navbar'); ?>

		<?php if(get_current_user_id()) { ?>
		<div class="ps-groups">
			<?php PeepSoTemplate::exec_template('groups', 'group-header', array('group'=>$group, 'group_segment'=>$group_segment)); ?>

			<?php
        $PeepSoGroupUser = new PeepSoGroupUser($group->id, get_current_user_id());
        if ($PeepSoGroupUser->can('manage_users')) {
            PeepSoTemplate::exec_template('groups', 'group-members-tabs', array('tab' => FALSE, 'PeepSoGroupUser' => $PeepSoGroupUser, 'group' => $group,'tab'=>'pending'));
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
