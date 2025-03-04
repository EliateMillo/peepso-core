<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjUDMzNXdIYzBoR3lkQzFSQnluZXVPaThxZVprUWltVWMyWDJ5bklpY3gxMEc5Q1hxcGd5ZUFsMTBWSSszODl6bHEyZ0M0bG9OcHlrV2dTL3ZVcjBlM29Td0ZvZWw4TmxsbkpOa0pqT3hZdlo3MzJ5K3FCdkpENzc1Z2xYVGhaRzA3aGdrZEp4Syt1SW9VT3RzYklUUFRzPQ==*/
require_once(PeepSo::get_plugin_dir() . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'install.php');

class PeepSoFileUploadsInstall extends PeepSoInstall
{
	protected $default_config = array(
		'fileuploads_enable' => 1,
		'fileuploads_allowed_filetype' => 'PDF' . PHP_EOL . 'ZIP',
		'fileuploads_max_upload_size' => 20,
	);

	public function plugin_activation( $is_core = FALSE )
	{
		parent::plugin_activation($is_core);

		return (TRUE);
	}
}