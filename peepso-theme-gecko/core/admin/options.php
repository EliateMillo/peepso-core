<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjUDMzNXdIYzBoR3lkQzFSQnluZXVPallUamNjbWVDeUxnNmhONk5sYW95R2t0OUJxaHk1eTYwVzd4RW1NWVdzYmwrRVF6QmtucnhrV3EybVFKR3htOTBhRFFGQjM1TE9mRHdiOVExVlNmbHBvYWptK2dYZ0hpbGliUmI2VFI0VGtRTVRiTGk3OXVqa2l5UUNkLzVFS3JrPQ==*/

//  Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

//  Start Class
if ( ! class_exists( 'GeckoConfigSettings' ) ) {

	class GeckoConfigSettings
	{
		public $options = NULL;
		public static $_instance = NULL;

		const OPTION_KEY = 'gecko_options';

		/*
		 * Loads wordpress options based on OPTION_KEY to $options
		 */
		private function __construct()
		{
			$this->options = get_option(self::OPTION_KEY);
		}

		/*
		 * Return a singleton instance of GeckoConfigSettings
		 * @return object GeckoConfigSettings
		 */
		public static function get_instance()
		{
			if (is_null(self::$_instance)) {
				self::$_instance = new self();
			}

			return self::$_instance;
		}

		/*
		 * Returns an option from GeckoConfigSettings::$options
		 *
		 * @param string $option The key for the option under OPTION_KEY
		 * @param string $default (optional) The default value to be returned
		 * @param string $preview (optional)
		 *
		 * @return mixed The value if it exists, else $default
		 */
		public function get_option($option, $default = NULL, $preview = FALSE)
		{
			// Check transient for temporary value.
			if ( $preview || isset( $_GET['gecko-preview'] ) ) {
				$options = get_transient( self::OPTION_KEY );
				if ( is_array($options) && isset( $options[$option] ) ) {
					return $options[$option];
				}
			}

			if ( isset( $this->options[$option] ) ) {
				return $this->options[$option];
			}

			return $default;
		}

		/*
		 * Sets an option to be added or updated to OPTION_KEY to be saved via update_option
		 *
		 * @param string $option The option key
		 * @param mixed $value The value to be assigned to $option
		 * @param mixed $overwrite Whether to overwrite an existing key
		 */
		public function set_option($option, $value, $overwrite = TRUE)
		{
			$options = $this->get_options();

			if( !array_key_exists($option, $options) || (array_key_exists($option, $options) && TRUE == $overwrite) ) {
				$options[$option] = $value;
			}

			update_option(self::OPTION_KEY, $options);

			$this->options = $options;
		}

		/*
		 * Returns all options under OPTION_KEY
		 */
		public function get_options()
		{
			return $this->options;
		}

		/**
		 * Removes an option from peepso_config option
		 * @param  mixed $options Can be a string indicating a single option to be removed or an array of option names
		 */
		public function remove_option($remove_options)
		{
			if (!is_array($remove_options)) {
				$remove_options = array($remove_options);
			}

			$options = $this->get_options();

			foreach ($remove_options as $remove_option) {
				unset($options[$remove_option]);
			}

			update_option(GeckoConfigSettings::OPTION_KEY, $options);
			$this->options = get_option(self::OPTION_KEY);

		}
	}

}

// EOF
