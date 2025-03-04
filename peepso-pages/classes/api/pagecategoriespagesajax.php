<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjUDMzNXdIYzBoR3lkQzFSQnluZXVPZ0ZyUWswV0pUOWtKVnRvL0FmTXdBaEdqWlNzTFM3V0duR3o4QTl4NkpaZ2xFcDZUOEV3bXYxMVZ4eXEvRy9SWnpVMEJaRmxuL1Z4NW9oWHZJY0hEdGM2NkFaNS95aXNkSzh5OVo3VDhhZXh4Wld0bTR6dzNXRUdFZDlWYlY0c3RDYXNkUi9TVjVEVTZvc2ZWUGswOWNL*/

class PeepSoPageCategoriesPagesAjax extends PeepSoAjaxCallback
{
	private $_page_id;

	protected function __construct()
	{
		parent::__construct();

		$this->_page_id = $this->_input->int('page_id');

		if(0 == $this->_page_id) {
			return;
		}
	}

    public function ajax_auth_exceptions()
    {
        $list_exception = array();
        $allow_guest_access = PeepSo::get_option('pages_allow_guest_access_to_pages_listing', 0);
        if($allow_guest_access) {
            array_push($list_exception, 'categories_for_page');
        }

        return $list_exception;
    }

	public function init($page_id)
	{
		$this->_page_id = $page_id;
	}

	public function categories_for_page(PeepSoAjaxResponse $resp)
	{
		$categories =  PeepSoPageCategoriesPages::get_categories_for_page($this->_page_id);

		if(count($categories)) {

			foreach ($categories as $category) {
			    // SQL safe, parsed
				$categories_response[] = PeepSoPageAjaxAbstract::format_response($category, PeepSoPageAjaxAbstract::parse_keys('pagecategory', $this->_input->value('keys', 'id', FALSE)), $this->_page_id);
			}
		}

		$resp->success(1);
		$resp->set('categories', $categories_response);
	}
}
