<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjUDMzNXdIYzBoR3lkQzFSQnluZXVPaklDYWdxaEhJVndqVVBKczVVRUl3OE9HZTBCMnF0Tktoa3NiWFJzaGFQcUYvK1N4MWlpUjA1dS9pbWt4Z0lETWJYbWsvbkg2UkFnWjBqVmV4U0FlL044K2ZVSUJaMWtsYkRVeGk5TllMWDR5TjVKZCt1VHYySjlaUHlRU1JUK2NuTTZxa1hGc2F0TTdlSkh0VlZMMGpQ*/

class PeepSoPageAjaxAbstract extends PeepSoAjaxCallback
{
	protected $_page_id;		// ID of the page
	protected $_user_id;		// ID of the current user
	protected $_model;			// Model instance applicable to the given endpoint

	protected function __construct()
	{
		parent::__construct();

		$this->_user_id = get_current_user_id();
		if($this->_request_method == 'post') {
			$this->_page_id = $this->_input->int('page_id');
		} else {
			$this->_page_id = $this->_input->int('page_id');
		}
	}

	/** GLOBAL PEEPSOGROUP(*)AJAX UTILITIES **/
	public static function parse_keys($default, $keys)
	{
		$raw_keys = explode(',', $keys);
		$keys = array();

		foreach($raw_keys as $key) {
			if(strstr($key, '.')) {
				$key = explode('.', $key);

				$class = $key[0];
				$method = $key[1];
			} else {
				$class = $default;
				$method = $key;
			}

			$keys[] = array('class' =>$class,'method'=>$method);
		}

		return $keys;
	}

	public static function format_response( $class, $keys, $page_id )
	{
		#var_dump($keys);die();
		$resp = array();
		foreach($keys as $key) {

			$class_key  = $key['class'];
			$class_name = "peepso$class_key";
			$method_key = $key['method'];

			// if the passed class instance is what we want data from
			if($class instanceof  $class_name) {
				$resp[$method_key] = $class->get($method_key);

				// Add markdown tag in the page description.
				if ('description' === $method_key && PeepSo::get_option_new('md_pages_about', 0)) {
					$resp[$method_key] = PeepSo::do_parsedown($resp[$method_key]);
				}
			} else {
				// PeepSoPage
				if('page' == $class_key) {
					if(!isset($peepsopage)) {
						$peepsopage = new PeepSoPage($page_id);
					}

					$tmp_class = $peepsopage;
				}

                // PeepSoPageFollower
                if('pagefollowerajax' == $class_key) {
                    if(!isset($PeepSoPageFollowerAjax)) {
                        $PeepSoPageFollowerAjax = PeepSoPageFollowerAjax::get_instance();
                        $PeepSoPageFollowerAjax->init($page_id);
                    }

                    $tmp_class = $PeepSoPageFollowerAjax;
                }

				// PeepSoPageUser
				if('pageuserajax' == $class_key) {
					if(!isset($PeepSoPageUserAjax)) {
						$PeepSoPageUserAjax = PeepSoPageUserAjax::get_instance();
						$PeepSoPageUserAjax->init($page_id);
					}

					$tmp_class = $PeepSoPageUserAjax;
				}

				// PeepSoPageUsers
				if('pageusersajax' == $class_key) {
					if(!isset($PeepSoPageUsersAjax)) {
						$PeepSoPageUsersAjax = PeepSoPageUsersAjax::get_instance();
						$PeepSoPageUsersAjax->init($page_id);
					}

					$tmp_class = $PeepSoPageUsersAjax;
				}

				// PeepSoPageCategoriesPages
				if('pagecategoriespagesajax' == $class_key) {
					if(!isset($PeepSoPageCategoriesPagesAjax)) {
						$PeepSoPageCategoriesPagesAjax = PeepSoPageCategoriesPagesAjax::get_instance();
						$PeepSoPageCategoriesPagesAjax->init($page_id);
					}

					$tmp_class = $PeepSoPageCategoriesPagesAjax;
				}

				if(strstr($method_key, '(')) {
					$method = explode('(', $method_key);
					$method_key = $method[0];

					$from = array('|',')');
					$to = array(',','');
					$keys = str_replace($from, $to, $method[1]);

					$resp[$class_key][$method_key] = $tmp_class->$method_key($keys);
				} else {
					$resp[$class_key][$method_key] = $tmp_class->$method_key();
				}
			}
		}

		return $resp;
	}
}
