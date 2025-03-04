<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjUDMzNXdIYzBoR3lkQzFSQnluZXVPZ0lDUkNSZ2hHTHIrMXhzeDRGRlZwS1ZyRWNySFVZNFJXQXgyVDBJMDBlNXJCVGN1Z3o3aXV1anJZbWhvNUxFUmJyRDZZbEtFQW8zWUh5YUVtYUp6THdHcFl3WTRQb3R1UnFIeVZHdG1JNHM3c2VPZ0VtcmwwYTIwN0VPdnprWmVzQXF2OUYwWDVjNnVNejNHd2pnTDBF*/

class PeepSoPageUsersAjax extends PeepSoAjaxCallback
{
	private $_page_id;
	private $_model;

	private $order_by_allowed = array(
        'pm_id'				,
        'pm_user_id'		,
        'pm_page_id'		,
        'pm_user_status' 	,
        'pm_joined'			,
        'pm_invited_by_id'	,
        'pm_accepted_by_id' ,
    );

	protected function __construct()
	{
		parent::__construct();

		$this->_page_id = $this->_input->int('page_id');

		if(0 == $this->_page_id) {
			return;
		}

		$this->_model = new PeepSoPageUsers($this->_page_id);
	}

    /**
     * Called from PeepSoAjaxHandler
     * Declare methods that don't need auth to run
     * @return array
     */
    public function ajax_auth_exceptions()
    {
        return array(
            'search',
        );
    }

	public function init($page_id)
	{
		$this->_page_id = $page_id;
		$this->_model = new PeepSoPageUsers($this->_page_id);
	}

    /**
     * GET
     * @param PeepSoAjaxResponse|NULL $resp
     * @param null $role
     * @param null $keys
     * @return array
     */
	public function search(PeepSoAjaxResponse $resp = NULL, $role = NULL, $keys = NULL)
	{
		$members_response = array();

		if(NULL == $role) {
			$role = $this->_input->value('role', 'member', FALSE); // SQL safe, switch
		}

		$page = $this->_input->int('page', 1);
		$query = stripslashes_deep($this->_input->value('query', '', false)); // sql safe

        // Additional peepso specific filters
		$args = array();

        // Avatar only
        $peepso_args['avatar_custom'] = $this->_input->int('peepso_avatar', 0);
        if ( 1 !== $peepso_args['avatar_custom'] ) {
            unset( $peepso_args['avatar_custom'] );
        }

        // Followed only
        $peepso_args['following'] = $this->_input->value('peepso_following', -1, FALSE); // SQL Safe
        if ( !in_array($peepso_args['following'], array(0,1)) || !class_exists('PeepSoFriendsPlugin')) {
            unset( $peepso_args['following'] );
        }

        // Blocked only
        $peepso_args['blocked'] = $this->_input->int('blocked', 0);
        if ( 1 !== $peepso_args['blocked'] ) {
            unset( $peepso_args['blocked'] );
        }

        // Gender filter
        $peepso_args['meta_gender'] = strtolower($this->_input->value('peepso_gender', '', FALSE)); // SQL Safe
        if ( !in_array( $peepso_args['meta_gender'], array('m','f') ) && strpos($peepso_args['meta_gender'], 'option_') === FALSE) {
            unset( $peepso_args['meta_gender'] );
        }

        $peepso_args = apply_filters('peepso_member_search_args', $peepso_args, $this->_input);

        if( is_array($peepso_args) && count($peepso_args)) {
            $args['_peepso_args'] = $peepso_args;
        }

		// Sorting
        $column = (PeepSo::get_option('system_display_name_style', 'real_name') == 'real_name' ? 'display_name' : 'user_login');

        $order_by	= $this->_input->value('order_by', $column, $this->order_by_allowed); // SQL Safe
		$order 		= strtoupper($this->_input->value('order', 'ASC', array('asc','desc')));

		if (NULL !== $order_by && strlen($order_by)) {
			if ('ASC' !== $order && 'DESC' !== $order) {
				$order = 'ASC';
			}
		}

        // default limit is 1 (NewScroll)
        $limit = $this->_input->int('limit', 1);

		$offset = ($page - 1) * $limit;

		switch($role) {
            case 'management':
                $users = $this->_model->get_management($args, $query, $order_by, $order, $offset, $limit);
                break;
			case 'owner':
				$users = $this->_model->get_owners($args, $query, $order_by, $order, $offset, $limit);
				break;
			case 'manager':
				$users = $this->_model->get_managers($args, $query, $order_by, $order, $offset, $limit);
				break;
			case 'moderator':
				$users = $this->_model->get_moderators($args, $query, $order_by, $order, $offset, $limit);
				break;
            case 'pending_user':
                $users = $this->_model->get_pending_user($args, $query, $order_by, $order, $offset, $limit);
                break;
			case 'pending_admin':
				remove_all_filters('peepso_user_search_args');
                $users = $this->_model->get_pending_admin($args, $query, $order_by, $order, $offset, $limit);
                break;
            case 'banned':
                $users = $this->_model->get_banned($args, $query, $order_by, $order, $offset, $limit);
                break;
			default:
				$users = $this->_model->get_members($args, $query, $order_by, $order, $offset, $limit);
		}

		if(count($users)) {

			if(NULL == $keys) {
				$keys = $this->_input->value('keys', 'id', FALSE); // SQL safe, parsed
			}

			foreach ($users as $user) {
			    $member = PeepSoPageAjaxAbstract::format_response($user, PeepSoPageAjaxAbstract::parse_keys('pageuser', $keys), $this->_page_id);

			    ob_start();
                do_action('peepso_action_render_user_name_before', $user->user_id);
                $before_fullname = ob_get_clean();

                ob_start();
                do_action('peepso_action_render_user_name_after', $user->user_id);
                $after_fullname = ob_get_clean();

                $member['fullname_before'] = $before_fullname;
                $member['fullname_after'] = $after_fullname;

				$members_response[] = $member;
			}
		}

		if(NULL == $resp) {
			return $members_response;
		}

		$resp->success(1);
		$resp->set('members', $members_response);
	}

	public function search_to_invite(PeepSoAjaxResponse $resp, $keys = NULL)
	{
		$users = array();
 		$page_user = new PeepSoPageUser($this->_page_id);

		// Find site users who do not have a record inside page_members for this page ID
		if ($page_user->can('invite')) {

			$args = array();
			$args_pagination = array();
			$page = $this->_input->int('page', 1);

			// Sorting
			$column = (PeepSo::get_option('system_display_name_style', 'real_name') == 'real_name' ? 'display_name' : 'user_login');

			$order_by	= $this->_input->value('order_by', $column, $this->order_by_allowed);
			$order		= $this->_input->value('order', ($order_by == $column ? 'ASC' : NULL), array('asc','desc'));

			if( NULL !== $order_by && strlen($order_by) ) {
				if('ASC' !== $order && 'DESC' !== $order) {
					$order = 'DESC';
				}

				$args['orderby']= $order_by;
				$args['order']	= $order;
			}

			$limit = 20;
			$limit = $this->_input->int('limit', $limit);
			$resp->set('page', $page);
			$args_pagination['offset'] = ($page-1)*$limit;
			$args_pagination['number'] = $limit;

			// Merge pagination args and run the query to grab paged results
			$args = array_merge($args, $args_pagination);
			$query = stripslashes_deep($this->_input->value('query', '', false)); // sql safe

			$users = $this->_model->search_to_invite($args, $query);

			$members_page = count($users->results);
			$members_found = $users->total;

			if (count($users->results) > 0) {

				if(NULL == $keys) {
					$keys = $this->_input->value('keys', 'id', FALSE); // SQL safe, parsed
				}

				foreach ($users->results as $user_id) {
					$user = new PeepSoPageUser($this->_page_id, $user_id);
					$members[] = PeepSoPageAjaxAbstract::format_response($user, PeepSoPageAjaxAbstract::parse_keys('pageuser', $keys), $this->_page_id);
				}

				if($members_found > 0)
				{
					$resp->success(TRUE);
					$resp->set('users', $members);
				}
				else
				{
					$resp->success(FALSE);
					$resp->error(__('No users found.', 'pageso'));
				}

			} else {
				$resp->success(FALSE);
				$resp->error(__('No users found.', 'pageso'));
			}
		}
	}

	/*
	 * Chainable methods
	 * These methods should only be directly called from other PeepSo(*)Ajax classes
	 * peepsopagesuseajax.method(key1|key2|key3)
	 */

	public function owners($keys)
	{
		return $this->search_members(NULL, 'owner', $keys);
	}

	public function admins($keys)
	{
		return $this->search_members(NULL, 'admin', $keys);
	}

	public function moderators($keys)
	{
		return $this->search_members(NULL, 'owner', $keys);
	}


}
