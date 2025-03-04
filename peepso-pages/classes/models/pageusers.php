<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjUDMzNXdIYzBoR3lkQzFSQnluZXVPakdtV010eEVCbDJQczdZb1FVMlZPdHF4SDRuMXB1UVlDMFdlMXdOSkozYjZwSGh0Vm5UcWF4S1B6b0QrOHl6TkRvdEEyY1VNN3B2VkRUSlFaN2hjV0h4T3Zla1RHOW9KTFY3eXkvUWFRUnhtU2N4MUtPSEE2MWIxMXN1OU4yR0dhdmNmL0UvTm8zRlkzUFBYZ3Y2ZzBM*/

class PeepSoPageUsers
{
	const TABLE = 'peepso_page_members';

	private $_page_id;

	private $_table;

	public function __construct($page_id)
	{
		global $wpdb;

		$this->_page_id = intval($page_id);
		$this->_table = $wpdb->prefix.PeepSoPageUsers::TABLE;

		if( !$this->_page_id > 0) {
			trigger_error('Invalid page_id');
		}
	}

	public function get_management($args = array(), $query = '', $order_by = 'pm_joined', $order = 'desc', $offset=NULL, $limit=NULL)
    {
        return $this->_get_users(array('member_owner','member_manager','member_moderator'), $args, $query, $order_by, $order, $offset, $limit);
    }

    public function get_owners_and_managers($args = array(), $query = '', $order_by = 'pm_joined', $order = 'desc', $offset=NULL, $limit=NULL)
    {
        return $this->_get_users(['member_owner','member_manager'], $args, $query,  $order_by, $order, $offset, $limit);
    }

	public function get_owners($args = array(), $query = '', $order_by = 'pm_joined', $order = 'desc', $offset=NULL, $limit=NULL)
	{
		return $this->_get_users('member_owner', $args, $query,  $order_by, $order, $offset, $limit);
	}

	public function get_managers($args = array(), $query = '', $order_by = 'pm_joined', $order = 'desc', $offset=NULL, $limit=NULL)
	{
		return $this->_get_users('member_manager', $args, $query,  $order_by, $order, $offset, $limit);
	}

	public function get_moderators($args = array(), $query = '', $order_by = 'pm_joined', $order = 'desc', $offset=NULL, $limit=NULL)
	{
		return $this->_get_users('member_moderator', $args, $query,  $order_by, $order, $offset, $limit);
	}

	public function get_members($args = array(), $query = '', $order_by = 'pm_joined', $order = 'desc', $offset = NULL, $limit=NULL)
	{
		return $this->_get_users('member', $args, $query,  $order_by, $order, $offset, $limit);
	}

    public function get_pending_user($args = array(), $query = '', $order_by = 'pm_joined', $order = 'desc', $offset = NULL, $limit=NULL)
    {
        return $this->_get_users('pending_user', $args, $query,  $order_by, $order, $offset, $limit);
    }

    public function get_pending_admin($args = array(), $query = '', $order_by = 'pm_joined', $order = 'desc', $offset = NULL, $limit=NULL)
    {
        return $this->_get_users('pending_admin', $args, $query,  $order_by, $order, $offset, $limit);
    }

    public function get_banned($args = array(), $query = '', $order_by = 'pm_joined', $order = 'desc', $offset = NULL, $limit=NULL)
    {
        return $this->_get_users('banned', $args, $query,  $order_by, $order, $offset, $limit);
    }

    private function _get_users($role, $args = array(), $query = '', $order_by = NULL, $order = NULL, $offset = NULL, $limit = NULL)
	{
		$args = array_merge($args, array(
			'_peepso_page_role' => $role
		));

		if( NULL !== $order_by && strlen($order_by) ) {
			if('ASC' !== $order && 'DESC' !== $order) {
				$order = 'DESC';
			}

			$args['_peepso_page_orderby'] = $order_by;
			$args['_peepso_page_order'] = $order;
		}

		if(NULL !== $offset && NULL !== $limit) {
			$args['offset']= $offset;
			$args['number']	= $limit;
		}

		add_action('peepso_pre_user_query', array(&$this, 'members_query'));
		$query_results = new PeepSoUserSearch($args, get_current_user_id(), $query);
		remove_action('peepso_pre_user_query', array(&$this, 'members_query'));

		$members = array();

		if(count($query_results->results)) {
			foreach($query_results->results as $row) {
			    $PeepSoPageUser = new PeepSoPageUser($this->_page_id, $row);
				$members[] = $PeepSoPageUser;
			}
		}

		return $members;
	}



	/**
	 * Count members and update the members_count meta key
	 * @return int
	 */
	public function update_members_count($role = NULL)
	{
		global $wpdb;


		if(NULL == $role) {
			// Count everyone with a "member*" status
			$meta ='peepso_page_members_count';
			$query = "SELECT COUNT(`pm_user_id`) as members_count FROM {$this->_table} LEFT JOIN `{$wpdb->prefix}".PeepSoUser::TABLE."` as `f` ON `{$this->_table}`.`pm_user_id` = `f`.`usr_id` WHERE `f`.`usr_role` NOT IN ('register', 'ban', 'verified') AND `pm_page_id`={$this->_page_id} AND `pm_user_status` LIKE 'member%'";
		} else {
			// Count everyone with a given role
			$meta ='peepso_page_'.$role.'_members_count';
			$query = "SELECT COUNT(`pm_user_id`) as members_count FROM {$this->_table} LEFT JOIN `{$wpdb->prefix}".PeepSoUser::TABLE."` as `f` ON `{$this->_table}`.`pm_user_id` = `f`.`usr_id` WHERE `f`.`usr_role` NOT IN ('register', 'ban', 'verified') AND `pm_page_id`={$this->_page_id} AND `pm_user_status`= '$role'";
		}

		$result = $wpdb->get_row($query, ARRAY_A);
		$members_count  = intval($result['members_count']);

		if(0 === $members_count && NULL == $role) {
			new PeepSoError('[GROUPS] Page member count should never be 0 (zero)');
		}

		// Update the post meta
		update_post_meta($this->_page_id, $meta, $members_count);

		return($members_count);
	}

	public function search_to_invite($args, $query)
	{
		add_action('peepso_pre_user_query', array(&$this, 'not_members_query'));
		$query_results = new PeepSoUserSearch($args, get_current_user_id(), $query);
		remove_action('peepso_pre_user_query', array(&$this, 'not_members_query'));

		return $query_results;
	}

	/**
	 * Modifies a WP_User_Query instance to only return users that are friends.
	 * @param  WP_User_Query $wp_user_query
	 */
	public function not_members_query(WP_User_Query $wp_user_query)
	{
		global $wpdb;

		$wp_user_query->query_from .= $wpdb->prepare(" LEFT JOIN {$this->_table} ON `{$wpdb->users}`.`ID` = `{$this->_table}`.`pm_user_id` AND `{$this->_table}`.`pm_page_id` = %d ", $this->_page_id);
		$wp_user_query->query_where .= " AND `{$this->_table}`.`pm_user_id` IS NULL ";

		return $wp_user_query;
	}

	public function members_query(WP_User_Query $wp_user_query)
	{
		global $wpdb;

		$wp_user_query->query_from .= $wpdb->prepare(" LEFT JOIN {$this->_table} ON `{$wpdb->users}`.`ID` = `{$this->_table}`.`pm_user_id` AND `{$this->_table}`.`pm_page_id` = %d ", $this->_page_id);
		
		$query = '';
		if (isset($wp_user_query->query_vars['_peepso_page_role'])) {
			$role = $wp_user_query->query_vars['_peepso_page_role'];

			if (!is_array($role)) {
				$role = array($role);
			}
	
			if (count($role) > 1) {
				$query .= " AND (";
				$sub_query ='';
				foreach($role as $r) {
					$sub_query .= "OR `pm_user_status` LIKE '{$r}%' ";
				}
	
				$sub_query = trim($sub_query, 'OR');
	
				$query.= $sub_query . " ) ";
	
			} else {
				$query .= " AND `pm_user_status` LIKE '{$role[0]}%'";
			}
	
			$wp_user_query->query_where .= $query;
		}

		$wp_user_query->query_orderby = ' ORDER BY ' . $wp_user_query->query_vars['_peepso_page_orderby'] . ' ' . $wp_user_query->query_vars['_peepso_page_order'];

		return $wp_user_query;
	}
}

// EOF