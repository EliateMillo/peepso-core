<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjUDMzNXdIYzBoR3lkQzFSQnluZXVPZ0ZyUWswV0pUOWtKVnRvL0FmTXdBaFVZT2Z2R3B5c2NPbVNqU0crUCtQeUlSUGNYWU1mdWh0b01XL3R2R0o0NngvaE1panFoNnFYeXBtZndQYmVWV3BBclByd1JuaGREMUNuUUNINHppUFQzUnFSYVJBazFlMlQrVlF0K1Jia1loWjMyWnpDTnBRdS9ZWXZCNWRJSm9u*/

class PeepSoPageCategoriesAjax extends PeepSoAjaxCallback
{

    /**
     * Called from PeepSoAjaxHandler
     * Declare methods that don't need auth to run
     * @return array
     */
    public function ajax_auth_exceptions()
    {
        $list_exception = array();
        $allow_guest_access = PeepSo::get_option('pages_allow_guest_access_to_pages_listing', 0);
        if($allow_guest_access) {
            array_push($list_exception, 'search');
        }

        return $list_exception;
    }
    
	/**
	 * GET
	 * @todo ordering
	 * @todo searching
	 * Search for categories matching the query.
	 * @param  PeepSoAjaxResponse $resp
	 */
	public function search(PeepSoAjaxResponse $resp)
	{
		$page = $this->_input->int('page', 1);
        $limit = $this->_input->int('limit', PeepSo::get_option('pages_categories_count', 1));
		$offset = ($page - 1) * $limit;

		$resp->set('page', $page);

		$PeepSoPageCategories = new PeepSoPageCategories(FALSE, NULL, $offset, $limit);
		$categories = $PeepSoPageCategories->categories;

		if (count($categories) > 0 || $page > 1) {

			$categories_response = array();

			foreach ($categories as $category) {
				$keys = $this->_input->value('keys', 'id', FALSE); // SQL safe, parsed
				$categories_response[] = PeepSoPageAjaxAbstract::format_response($category, PeepSoPageAjaxAbstract::parse_keys('pagecategory', $keys), $category->get('id'));
			}

			$resp->success(TRUE);
			$resp->set('page_categories', $categories_response);
		} else {
			$resp->success(FALSE);
			$resp->error(__('No Categories Found.', 'pageso'));
		}
	}
}

// EOF
