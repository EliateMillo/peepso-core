{{

var memberActions, followerActions;

data = data || {};
memberActions = data.member_actions || [];
followerActions = data.follower_actions || [];

_.each( memberActions.concat( followerActions ), function( item ) {
	if ( _.isArray( item.action ) ) {

}}

<span class="ps-dropdown ps-dropdown--left ps-dropdown--follow ps-js-dropdown">
	<a href="javascript:" class="ps-focus__cover-action ps-js-dropdown-toggle {{= item.class }}">
		<i class="{{= item.icon }}"></i>
		<span>{{= item.label }}</span>
		<img class="ps-loading" src="<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjUDMzNXdIYzBoR3lkQzFSQnluZXVPZ1RjcFhDSVd2MW0wT29FeVRuYWgwN3BJQnQrRTlXd0E1cVNWSjZBT0ZBUGMveTFTbXA4cWFQdTdvdG1PcnJxWEZPSEdFdlR1ZjFwM05hd3B0bWNITThWZ0RtTVkyK0Z3VDkyTmM5aDBYN1ZaTHJNMkpIelIvMzlwQ2tlL3RRUmVaYUVSVUovZU9EQlZZRFM3cjBZZGMx*/ echo PeepSo::get_asset('images/ajax-loader.gif') ?>" />
	</a>
	<div class="ps-dropdown__menu ps-js-dropdown-menu">
		{{ _.each( item.action, function( item ) { }}
		<a class="ps-dropdown__page ps-js-page-member-action"
				{{= item.action ? 'data-method="' + item.action + '"' : 'disabled="disabled"' }}
				data-confirm="{{= item.confirm }}"
				{{ if ( item.args ) _.each( item.args, function( value, key ) { }}
				data-{{= key }}="{{= value }}"
				{{ }); }}
		>
			<div class="ps-dropdown__page-title">
				<i class="{{= item.icon }}"></i>
				<span>{{= item.label }}</span>
			</div>
			<div class="ps-dropdown__page-desc">{{= item.desc }}</div>
		</a>
		{{ }); }}
	</div>
</span>

{{

	} else {

}}

<a role="button" aria-label="{{= item.label }}"
		{{ if (item.redirect) { }}
			href="{{= item.redirect }}"
			class="ps-focus__cover-action {{= item.class }}"
		{{ } else { }}
			class="ps-focus__cover-action ps-js-page-member-action {{= item.class }}"
			href="javascript:"
			{{= item.action ? 'data-method="' + item.action + '"' : 'disabled="disabled"' }}
			data-confirm="{{= item.confirm }}"
			{{ if ( item.args ) _.each( item.args, function( value, key ) { }}
			data-{{= key }}="{{= value }}"
			{{ }); }}
		{{ } }}
>
	<i class="{{= item.icon }}"></i>
	<span>{{= item.label }}</span>
	<img class="ps-loading" src="<?php echo PeepSo::get_asset('images/ajax-loader.gif') ?>" />
</a>

{{

	}
});

}}
