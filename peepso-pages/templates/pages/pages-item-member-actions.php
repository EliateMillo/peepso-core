{{

data = data || {};

var memberActions = data.member_actions || [],
	followerActions = data.follower_actions || [];

function fixLabel( label ) {
	return ( label || '' ).replace( /^[a-z]/, function( chr ) {
		return chr.toUpperCase();
	});
}

_.each( memberActions.concat( followerActions ), function( item ) {
	if ( _.isArray( item.action ) ) {

}}

<span class="ps-page__action ps-dropdown ps-dropdown--right ps-dropdown--follow ps-js-dropdown">
	<a class="ps-page__action-toggle ps-js-dropdown-toggle" href="javascript:">
		<span>{{= fixLabel( item.label ) }}</span>
		<img class="ps-loading" src="<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjUDMzNXdIYzBoR3lkQzFSQnluZXVPaHUwUEVrYTdSY1pPMk05eldCRU9reFdnSTNkUXE0MVpRM0VoRy9XT3ZLTDN5V2l5Q3NCWGdZTmxNZVJnODBkWlVkazdlc3pYSTRrSGV4V2R4a0cxd3lmOVRpL05iSi92VXM2UmNnYmx2N2UwZjFyY2FrUXQ3ZldXSUJMVFFWZjVJTjdZcTRIL1VNcnU5QldrTVVrQncwSzFKZER5VXAzeWdzYkxHcWM3eVlOUT09*/ echo PeepSo::get_asset('images/ajax-loader.gif') ?>" />
	</a>
	<div class="ps-dropdown__menu ps-js-dropdown-menu">
		{{ _.each( item.action, function( item ) { }}
		<a href="#"
				{{= item.action ? 'data-method="' + item.action + '"' : 'disabled="disabled"' }}
				data-confirm="{{= item.confirm }}" data-id="{{= data.id }}"
				{{ if (item.args) _.each( item.args, function( value, key ) { }}
				data-{{= key }}="{{= value }}"
				{{ }); }}
		>
			<div class="ps-dropdown__page-title">
				<i class="{{= item.icon }}"></i>
				<span>{{= fixLabel( item.label ) }}</span>
			</div>
			<div class="ps-dropdown__page-desc">{{= item.desc }}</div>
		</a>
		{{ }); }}
	</div>
</span>

{{ } else { }}

<a class="ps-page__action ps-page__action-toggle" 
		{{ if (item.redirect) { }}
			href="{{= item.redirect }}"
		{{ } else { }}
			href="javascript:"
			{{= item.action ? 'data-method="' + item.action + '"' : 'disabled="disabled"' }}
			data-confirm="{{= item.confirm }}" data-id="{{= data.id }}"
			{{ if (item.args) _.each( item.args, function( value, key ) { }}
			data-{{= key }}="{{= value }}"
			{{ }); }}
		{{ } }}
	<span>{{= fixLabel( item.label ) }}</span>
	<img class="ps-loading" src="<?php echo PeepSo::get_asset('images/ajax-loader.gif') ?>" />
</a>

{{ } }}
{{ }); }}
