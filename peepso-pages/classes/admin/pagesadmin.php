<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjUDMzNXdIYzBoR3lkQzFSQnluZXVPai9Yd2J4WUxoOWdvQWtRcXlVbFViUHR0TXBva3BVRm40TFJGblJlblozYjh4ZHhneUdWdlBxSWVjZ210MENvZkpzOFpLR2NMWEFIU3B1dGVDMzVtcjFza1VqeGo2VUs0WXFQRnh2cS9VNWhUZmJCQWI4dURSYVdITkE3QlI4RUR3ZWJBb0FkMkp2RVVnNzRzRG9YcXNE*/
/*
 * Performs tasks for Admin page requests
 * @package PageSo
 * @author PeepSo
 */

class PeepSoPagesAdmin
{
	private function __construct()
	{

	}

	public static function admin_page()
	{
		PeepSoTemplate::exec_template('admin', 'pages', array() );
	}

	public static function admin_header($title)
	{
		echo '<h2><img src="', PeepSo::get_asset('images/admin/logo.png'), '" width="150" />';
		echo ' v' . PeepSoPagesPlugin::PLUGIN_VERSION;

		if(strlen(PeepSoPagesPlugin::PLUGIN_RELEASE)) {
			echo "-" . PeepSoPagesPlugin::PLUGIN_RELEASE;
		}

		echo ' - ' ,  $title , '</h2>', PHP_EOL;
	}
}