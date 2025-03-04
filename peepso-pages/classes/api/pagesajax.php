<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjUDMzNXdIYzBoR3lkQzFSQnluZXVPZ3dUSS9ybm9TTnpCQ0I1NHlVdUY1K3FNUDhaTW81WUo5dWxwOE5IODIrZ0hEUHZFZnV5Q0NrMEJVb3VSY3Z6WTVGSDJyemVZSlJUYkpWRndNWTE5elZsZ0ZIV3V4Z0IxR2dLZStGZElRMDI4U29OY1VOU25KWGRHTTE3bDVsWWw3UmFCMk5HbW90T0w0VG1zS3dEVy9t*/

class PeepSoPagesAjax extends PeepSoAjaxCallback
{
	/** GROUP SEARCH & LISTING **/

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
	 * Search for pages matching the query.
	 * @param  PeepSoAjaxResponse $resp
	 */
	public function search(PeepSoAjaxResponse $resp)
	{
	    $order_by_default = PeepSo::get_option('pages_default_sorting', 'id');
	    $order_default = PeepSo::get_option('pages_default_sorting_order', 'DESC');

        $order_by = $this->_input->value('order_by', $order_by_default, false); // SQL safe
        $order = strtoupper($this->_input->value('order', $order_default, array('asc','desc')));

        $writable_only = $this->_input->int('writable_only' ,0);

        $open_only = $this->_input->int('open_only' ,0);

        $search_mode = $this->_input->value('search_mode' ,'exact',['exact','any']);

		$page = $this->_input->int('page', 1);
		$query = stripslashes_deep($this->_input->value('query', '', false)); // SQL safe
		$user_id = $this->_input->int('user_id', 0);
		$category = $this->_input->int('category', 0);
		$limit = $this->_input->int('limit', 1);

		if(-1 == $this->_input->value('category',0, FALSE)) { // SQL safe, forced INT
		    $category = -1;
        } else {
		    $category = intval($category);
        }

		if (NULL !== $order_by && strlen($order_by)) {
			if ('ASC' !== $order && 'DESC' !== $order) {
				$order = 'ASC';
			}
		}

		$offset = ($page - 1) * $limit;

		$resp->set('page', $page);

		$PeepSoPages = new PeepSoPages();

		// Add "+ 1" to the limit value to detect if next page is available.
		$pages = $PeepSoPages->get_pages($offset, $limit + 1, $order_by, $order, $query, $user_id, $category, $search_mode);

        if($page == 1) {
            (new PeepSo3_Search_Analytics())->store($query, 'pages');
        }

		if (count($pages) > 0 || $page > 1) {

			// Set next page flag and reset pages count according to the limit value.
			$has_next = false;
			if (count($pages) > $limit) {
				$has_next = true;
				$pages = array_slice($pages, 0, $limit);
			}

			$pages_response = array();

			foreach ($pages as $page) {

                $PeepSoPageUser = new PeepSoPageUser($page->get('id'), get_current_user_id());

                if (!$PeepSoPageUser->can('access')) {
                    continue;
                }

                if ($writable_only && !$PeepSoPageUser->can('post')) {
                    continue;
                }

                if ($open_only && !$page->is_open) {
                    continue;
                }

				$keys = $this->_input->value('keys', 'id', FALSE); // SQL safe, parsed

                if($PeepSoPageUser->can('manage_users') && !stristr($keys,'pending_admin_members_count')) {
                    $keys.=',pending_admin_members_count';
                }

                if($PeepSoPageUser->can('manage_users') && !stristr($keys,'pending_user_members_count')) {
                    $keys.=',pending_user_members_count';
                }

				$pages_response[] = PeepSoPageAjaxAbstract::format_response($page, PeepSoPageAjaxAbstract::parse_keys('page', $keys), $page->get('id'));
			}

			$resp->success(TRUE);
			$resp->set('pages', $pages_response);
			$resp->set('has_next', $has_next);
		} else {
			$resp->success(FALSE);

			if($user_id) {
                $message = (get_current_user_id() == $user_id) ? __('You are not following any pages yet', 'pageso') : sprintf(__('%s is not following any pages yet', 'pageso'), PeepSoUser::get_instance($user_id)->get_firstname());
                $resp->error(PeepSoTemplate::exec_template('profile', 'no-results-ajax', array('message' => $message), TRUE));
            } else {
                $message = __('No pages found', 'pageso');
                $resp->error(PeepSoTemplate::exec_template('general', 'no-results-ajax', array('message' => $message), TRUE));
            }
		}
	}

	public function move_post(PeepSoAjaxResponse $resp) {
	    if(PeepSo::is_admin()) {
            $post_id = $this->_input->int('post_id', 0);
            $page_id = $this->_input->int('page_id', 0);

            delete_post_meta($post_id, 'peepso_page_id');

            if (0 != $page_id) {
                update_post_meta($post_id, 'peepso_page_id', $page_id);

                $PeepSoPageUser = new PeepSoPageUser($page_id, $post->post_author);
                if (!$PeepSoPageUser->is_member) {
                    $invite[] = $PeepSoPageUser->user_id;
                }


                $resp->set('invite', $invite);

            }

            $resp->success(TRUE);
        }
    }
}
// EOF
