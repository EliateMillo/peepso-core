<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjUDMzNXdIYzBoR3lkQzFSQnluZXVPZ3BkK0s3b3MvNk9TVGdhQ0IxL2dQZHRpKzNTTWNVRVlPcFNPczZpVHU3MFlLTGdqcHBmc0NFcS8vRC8zeHlHSWpyTVZxa21udkF5eTdSaUcyOUVWMHMwMXVlS0pieEtuSDRsRTVCNnhTb2RkQnRSbGxkVmVSRmFsQjRnSUNUdXRzPQ==*/

/**
 * Class PeepSoPages
 *
 * This class is used for getting data sets containing multiple pages, based on various conditions/filters
 *
 */

class PeepSoPages
{
	public $user_id;
    public $search;
    public $search_mode;
	public $category;
	public $where_clauses;

	public function get_pages($offset, $limit, $order_by, $order, $search, $user_id = 0, $category = 0, $search_mode = 'exact')
	{
		global $wpdb;

		$this->user_id = $user_id;
        $this->search = $search;
        $this->search_mode = $search_mode;
		$this->category = $category;

		$args = array(
			'orderby' => $order_by,
			'order' => $order,
			'posts_per_page' => $limit,
			'offset' => $offset,
			'ignore_sticky_posts' => true
		);

		add_filter('posts_clauses_request', array(&$this, 'filter_post_clauses'), 10, 2);

        #6666 GeoMyWp hooks
        $args = apply_filters( 'peepso_filter_pages_query_args', $args );

		$post_query = new WP_Query($args);

		remove_filter('posts_clauses_request', array(&$this, 'filter_post_clauses'));

		$pages = array();

		while ($post_query->have_posts()) {
			$post_query->the_post();
			$post = $post_query->post;
			
			if($post->post_type != PeepSoPage::POST_TYPE) { 
				new PeepSoError('[GROUPS] PeepSoPages::get_pages() encountered a wrong post_type');
				continue; 
			}
			
			$pages[] = new PeepSoPage($post->ID);
		}

		wp_reset_postdata();

		return $pages;
	}

	public function filter_post_clauses($clauses, $query)
	{
		global $wpdb;

		$tbl_ps_members = $wpdb->prefix.PeepSoPageUsers::TABLE;
		$tbl_wp_posts = $wpdb->posts;
		$tbl_ps_categories = $wpdb->prefix.PeepSoPageCategoriesPages::TABLE;

		// Add the default groupby clause anyway, to prevent duplicate records retrieved, one instance of this behavior is showing comments with the friends add-on enabled
		$clauses['groupby'] = "`$tbl_wp_posts`.`ID`";

		$clauses['join'] .= " LEFT JOIN {$wpdb->postmeta} gp ON {$wpdb->posts}.ID = gp.post_id AND gp.meta_key = 'peepso_page_privacy' ";

		// If looking at a specific user's listing we need only pages he is a member of
		if ($this->user_id !== 0) {
			$clauses['join'] .= "  JOIN `$tbl_ps_members` as `peepso_page_member` ON " .
			" `$tbl_wp_posts`.`ID` = `peepso_page_member`.`pm_page_id` " .
			" AND `peepso_page_member`.`pm_user_status` LIKE 'member%' " .
			" AND  `peepso_page_member`.`pm_user_id` = '{$this->user_id}' ";
		}

		// if looking at specific category
		$clauses['join'] .= " LEFT JOIN `$tbl_ps_categories` as `gc` ON " .
			" `$tbl_wp_posts`.`ID` = `gc`.`pm_page_id` ";
		
		// Set post_type
		$where = " AND `$tbl_wp_posts`.`post_type`='".PeepSoPage::POST_TYPE."'";

		// Search
		if(!empty($this->search)) {
		    $this->search = trim($this->search);
		    $this->search = str_replace('%', '\%', $this->search);
		    if($this->search_mode == 'any') {

		        // multi-word search
                if(strstr($this->search, ' ')) {
                    $search = explode(' ', $this->search);
                } else {
                    $search = [$this->search];
                }

                $where_words = '';
                foreach($search as $s) {
                    $s="%$s%";
                    $where_words .= "OR( `$tbl_wp_posts`.`post_title` LIKE '$s' OR `$tbl_wp_posts`.`post_content` LIKE '$s') ";
                }

                $where .= " AND ( " . trim($where_words,'OR') . " ) ";
            } else {
		        // exact phrase search
                $s = "%{$this->search}%";
                $where .= " AND ( `$tbl_wp_posts`.`post_title` LIKE '$s' OR `$tbl_wp_posts`.`post_content` LIKE '$s') ";
            }
		}
		
		// Handle unpublished pages
		if(!PeepSo::is_admin()) {
			$uid = get_current_user_id();
			if ($uid == 0) {
				$where .= 	" AND (`$tbl_wp_posts`.`post_status` = 'publish' AND gp.meta_value != '".PeepSoPagePrivacy::PRIVACY_SECRET."')";
			} else {
				$where .= 	" AND (`$tbl_wp_posts`.`post_status` = 'publish' AND (gp.meta_value != '".PeepSoPagePrivacy::PRIVACY_SECRET."' OR (gp.meta_value = '".PeepSoPagePrivacy::PRIVACY_SECRET."' AND EXISTS (SELECT `pm_id` FROM `$tbl_ps_members` WHERE `$tbl_ps_members`.`pm_user_id`=$uid AND `$tbl_ps_members`.`pm_page_id`=`$tbl_wp_posts`.`ID` AND `$tbl_ps_members`.`pm_user_status` IN ('member', 'pending_user', 'member_moderator', 'member_owner','member_manager')))))";
			}
		} else {
			$where .= 	" AND `$tbl_wp_posts`.`post_status` = 'publish'";
		}

		// handle filter page by category
		// display uncategories
		if($this->category == -1) {
			$where .= " AND `gc`.`pm_page_id` IS NULL ";
		} elseif($this->category > 0) {
			$where .= " AND `gc`.`pm_cat_id` = " . $this->category . " ";
		}

		if ($query->query['orderby'] == 'meta_members_count') {
			$clauses['join'] .= " LEFT JOIN {$wpdb->postmeta} page_count ON {$wpdb->posts}.ID = page_count.post_id AND page_count.meta_key = 'peepso_page_members_count' ";

			$clauses['orderby'] = ' CAST(page_count.meta_value AS UNSIGNED) ' . $query->query['order'];
		}

		// Override the where clause completely
		$clauses['where'] = $where . $this->where_clauses;

		return $clauses;
	}

	public static function admin_count_pages($search='', $show='all')
	{
		global $wpdb;

		$where = " WHERE `{$wpdb->posts}`.`post_type`='peepso-page' ";

		if(!empty($search)) {
			$where .= " AND `{$wpdb->posts}`.`post_title` LIKE '%" . $search . "%'";
		}

		if($show !== 'all') {
			$where .= " AND `{$wpdb->posts}`.`post_status` = '" . $show . "'";
		}

		$rowcount = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} " . $where);

    	return $rowcount;
	}

	public static function admin_get_pages($offset = 0, $limit = 10, $orderby = 'post_title', $sort = 'desc', $search='', $show='all')
	{
		global $wpdb;

		$orderby = ($orderby === NULL) ? 'post_title' : $orderby;
		$sort = ($sort === NULL) ? 'desc' : $sort;

		$clauses=array('join'=>'', 'where'=> []);

		$clauses['join'] .=
			" LEFT JOIN `{$wpdb->posts}` ON `pm_page_id`=`{$wpdb->posts}`.`ID` ";

		$clauses['where'][] = "`pm_user_status` LIKE 'member%'";

		#$clauses['where'][]="(`{$wpdb->posts}`.`post_status`='publish' OR `{$wpdb->posts}`.`post_status`='pending')";

		$clauses['where'][] = "`{$wpdb->posts}`.`post_type`='peepso-page'";

		if(!empty($search)) {
			$clauses['where'][] = "`{$wpdb->posts}`.`post_title` LIKE '%" . $search . "%'";
		}

		if($show !== 'all') {
			$clauses['where'][] = "`{$wpdb->posts}`.`post_status` = '" . $show . "'";
		}

		$sql = "SELECT DISTINCT `a`.`pm_page_id`, (SELECT COUNT(*) FROM `{$wpdb->prefix}" . PeepSoPageUsers::TABLE . "` WHERE  pm_page_id = a.pm_page_id) as members_count FROM `{$wpdb->prefix}" . PeepSoPageUsers::TABLE . "` a";

		$sql .= $clauses['join'];

		$sql .= ' WHERE ' . implode(' AND ', $clauses['where']);

		$sql .= " ORDER BY `{$orderby}` {$sort}";

		if ($limit) {
			$sql .= " LIMIT {$offset}, {$limit}";
		}

		$page_ids = $wpdb->get_results($sql, ARRAY_A);

		$pages = array();
		if(count($page_ids)) {
			foreach ($page_ids as $page_id) {
				$pages[] = new PeepSoPage($page_id['pm_page_id']);
			}
		}

		return $pages;
	}
}

// EOF