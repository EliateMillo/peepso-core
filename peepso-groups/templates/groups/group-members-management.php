<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjUDMzNXdIYzBoR3lkQzFSQnluZXVPaVNFMXE1RHVidWZUZmoxY1krbmZYUUNxRHBpWTl1U2lKVnBTRTlFK2F2MzcvUWQzUGlLeklLVDk3VGkxUmZRaWpGQjZHUHFMbnpJME5McVQwejVQSDBEdEZSa3BVU0s1RU1lZHBtd1pGQmI3M2VVcTQvNyt0ZDhwWDRIU05ITzg4cG5qVURaQm5iek4ya210N2lPOFhT*/ $PeepSoGroupUser = new PeepSoGroupUser($group->id); ?>
<div class="peepso">
	<div class="ps-page ps-page--group-members">
		<?php PeepSoTemplate::exec_template('general','navbar'); ?>

		<?php if($PeepSoGroupUser->can('access')) { ?>
		<div class="ps-groups">
			<?php PeepSoTemplate::exec_template('groups', 'group-header', array('group'=>$group, 'group_segment'=>$group_segment)); ?>

			<?php if (! get_current_user_id()) { PeepSoTemplate::exec_template('general','login-profile-tab'); } ?>

			<?php
        $PeepSoGroupUser = new PeepSoGroupUser($group->id, get_current_user_id());
        PeepSoTemplate::exec_template('groups', 'group-members-tabs', array('tab' => 'management', 'PeepSoGroupUser' => $PeepSoGroupUser, 'group' => $group));
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
