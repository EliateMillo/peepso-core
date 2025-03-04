<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjUDMzNXdIYzBoR3lkQzFSQnluZXVPaThxZVprUWltVWMyWDJ5bklpY3gxMEc5Q1hxcGd5ZUFsMTBWSSszODl6bHEyZ0M0bG9OcHlrV2dTL3ZVcjBlM29Td0ZvZWw4TmxsbkpOa0pqT3hZdlo3MzJ5K3FCdkpENzc1Z2xYVGhaRzB5eUhkN3BmRWFWUWFlYXdVYU1uWFU0PQ==*/
require_once(PeepSo::get_plugin_dir() . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'install.php');

class PeepSoLimitUsersInstall extends PeepSoInstall
{
	public function plugin_activation( $is_core = FALSE )
	{
		parent::plugin_activation($is_core);
		return (TRUE);
	}
}