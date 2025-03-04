{{ _.each( data.member_passive_actions, function( item ) { }}
{{ if ( _.isArray( item.action ) ) { }}

<a href="javascript:" class="ps-member__action ps-dropdown__toggle ps-js-dropdown-toggle">
	<i class="{{= item.class ? item.class : 'gcis gci-cog' }}"></i><span><?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjUDMzNXdIYzBoR3lkQzFSQnluZXVPaVJMY0MwQUhWd3VBd1gveU90cWc3d2NiQWNrYVhoQUdiV1U1ZE4yK0JvNDJKdFFHNGFyU1RBWktidlFVWEtNR2s5N21aM3pqSHJSOTJqZDM5UWx2endabmpPM2VYNng2VUJhbGpmNTJkS0xKZDVtc3JybTFsN0NJaUhhMVF3T2VLNWR1dGZLbVRoVWY2RHpEWE1rTFZZSS80QVlWV1duWVRxc1Bnd0NIN1BwZz09*/ echo __('Manage','pageso');?></span>
</a>
<div class="ps-dropdown__menu ps-js-dropdown-menu" style="display:none">
	{{ _.each( item.action, function( subitem ) { }}
	<a href="#" class="ps-js-page-member-action" {{= subitem.action ? 'data-method="' + subitem.action + '"' : 'disabled="disabled"' }}
			data-confirm="{{= subitem.confirm }}" data-id="{{= data.id }}" data-passive_user_id="{{= data.passive_user_id }}"
			{{ if (subitem.args) _.each( subitem.args, function( value, key ) { }}
			data-{{= key }}="{{= value }}"
			{{ }); }}
	>
		<span>{{= ( subitem.label || '' ).charAt(0).toUpperCase() + ( subitem.label || '' ).slice(1) }}</span>
		<img src="<?php echo PeepSo::get_asset('images/ajax-loader.gif') ?>" style="padding-left:5px;display:none" />
	</a>
	{{ }); }}
</div>

{{ } else { }}

<button class="ps-member__action ps-js-page-member-action" {{= item.action ? 'data-method="' + item.action + '"' : 'disabled="disabled"' }}
		data-confirm="{{= item.confirm }}" data-id="{{= data.id }}" data-passive_user_id="{{= data.passive_user_id }}"
		{{ if (item.args) _.each( item.args, function( value, key ) { }}
		data-{{= key }}="{{= value }}"
		{{ }); }}
>
	<span>{{= ( item.label || '' ).charAt(0).toUpperCase() + ( item.label || '' ).slice(1) }}</span>
	<img src="<?php echo PeepSo::get_asset('images/ajax-loader.gif') ?>" style="padding-left:5px;display:none" />
</button>

{{ } }}
{{ }); }}
