<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjUDMzNXdIYzBoR3lkQzFSQnluZXVPaFdJUVJSYVlOT3I0VDlmbE8wTzJJYTNQMEtQRXd5a2E1NmlzRVlVVWFsaG4reUJHcC8rTXpOMUxpc3NYYzYyWWs2ckFJaE9QdCtEZDdkMGJjUk53YjNvR1J2OUYrY1pBcUE4TVJMSGFqRkQvN2cwT0JEQjdFRHNnTWVDdnNTeFdUQTl2MDB1QVBSQWhkdnBTbjFqQVdD*/

class PeepSoMessagesCron
{
	private static $_instance = NULL;

	/**
	 * Class constructor
	 */
	private function __construct()
	{

	}

	/**
	 * Retrieve singleton class instance
	 * @return PeepSoMessagesCron instance
	 */
	public static function get_instance()
	{
		if (NULL === self::$_instance)
			self::$_instance = new self();
		return (self::$_instance);
	}
}

// EOF
