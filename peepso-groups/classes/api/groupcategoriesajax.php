<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjUDMzNXdIYzBoR3lkQzFSQnluZXVPaHUzR0k0OVlEU281SEZmdGJkajI1ellUY0F4ZXhvZEVSYzhpNm9WOFF2RFZhL0ZmT3V4eUhCa3VMOGdpeDJSTG9uZjh5UWNEU2JiTHB4cFlySHUwOW5KNUlTRXJSZGEwYnBZakhQT3hTczU1dDQ0dXU4SkMvUmlPQW54N0hIMlM0RU03WnJyeW5qcHIxbUdoczlsWU1N*/

class PeepSoGroupCategoriesAjax extends PeepSoAjaxCallback
{

    /**
     * Called from PeepSoAjaxHandler
     * Declare methods that don't need auth to run
     * @return array
     */
    public function ajax_auth_exceptions()
    {
        $list_exception = array();
        $allow_guest_access = PeepSo::get_option('groups_allow_guest_access_to_groups_listing', 0);
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
        $limit = $this->_input->int('limit', PeepSo::get_option('groups_categories_count', 1));
		$offset = ($page - 1) * $limit;

		$resp->set('page', $page);

		$PeepSoGroupCategories = new PeepSoGroupCategories(FALSE, NULL, $offset, $limit);
		$categories = $PeepSoGroupCategories->categories;

		if (count($categories) > 0 || $page > 1) {

			$categories_response = array();

			foreach ($categories as $category) {
				$keys = $this->_input->value('keys', 'id', FALSE); // SQL safe, parsed
				$categories_response[] = PeepSoGroupAjaxAbstract::format_response($category, PeepSoGroupAjaxAbstract::parse_keys('groupcategory', $keys), $category->get('id'));
			}

			$resp->success(TRUE);
			$resp->set('group_categories', $categories_response);
		} else {
			$resp->success(FALSE);
			$resp->error(__('No Categories Found.', 'groupso'));
		}
	}
}

// EOF
