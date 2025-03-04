<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjUDMzNXdIYzBoR3lkQzFSQnluZXVPZ0ZyUWswV0pUOWtKVnRvL0FmTXdBaHZocHNjbGwwbGUrWDhMaHhuUlptVXVaeVJqQmJKb1NIRlBnSWxZeC9ZOWlQQkMxaWpnRkxUQlRha0l2cWtNbGdzM0Y2a3VSZ2VuREVTMVpwSERhYzNqWFNqTE9wOHdvbkJJM0ZqZVZCdUt4NUpMYlRVNDZGWDZCaHlPQkdNRW0r*/
/**
 * Class PeepSoPageCategoriesPages
 *
 * Manage/list all possible relationships between PageCategory and Page
 *
 * Add 		Page 				to 		PageCategory	(1 to 1)
 * Remove 	Page 				from	PageCategory	(1 to 1)
 * Get 		PageCategories 	for 	Page			(1 to many)
 * Get 		Pages				for 	PageCategory	(1 to many)
 */
class PeepSoPageCategoriesPages
{
    const TABLE = 'peepso_page_categories';

    /**
     * Add a Page to one or more PageCategory
     *
     * @param $page_id int
     * @param $cat_id array|int
     * @return bool
     */
    public static function add_page_to_categories($page_id, $cat_ids)
    {
        global $wpdb;

        // remove all categories
        self::remove_page_from_category($page_id, 0, TRUE);


        // $cat_id can be an array or an int
        if (is_int($cat_ids)) {
            $cat_ids = array($cat_ids);
        }

        // loop through categories and add
        foreach ($cat_ids as $cat_id) {
            $data = array(
                'pm_cat_id' => $cat_id,
                'pm_page_id' => $page_id
            );

            $wpdb->insert($wpdb->prefix . self::TABLE, $data);

            self::update_stats_for_category($cat_id);
        }

        return TRUE;
    }

    public static function remove_page_from_category($page_id, $cat_id, $recount = TRUE)
    {
        global $wpdb;
        $where = array(
            'pm_cat_id' => $cat_id,
            'pm_page_id' => $page_id,
        );

        // removing page from all categories
        if(0 == $cat_id) {
            unset($where['pm_cat_id']);
        }

        $wpdb->delete($wpdb->prefix . self::TABLE, $where);

        return TRUE;
    }

    public static function get_categories_id_for_page($page_id)
    {
        global $wpdb;

        $query = "SELECT `pm_cat_id` as `cat_id` FROM {$wpdb->prefix}" . self::TABLE . " WHERE `pm_page_id`=%d";
        $query = $wpdb->prepare($query, $page_id);
        $cat_ids = $wpdb->get_results($query);

        $resp = array();

        foreach ($cat_ids as $cat_id) {
            $resp[] = (int)$cat_id->cat_id;

        }
        return $resp;
    }

    public static function get_categories_for_page($page_id)
    {
        $cat_ids = self::get_categories_id_for_page($page_id);

        $resp = array();

        if (count($cat_ids)) {
            foreach ($cat_ids as $cat_id) {
                $resp[$cat_id] = new PeepSoPageCategory($cat_id);
            }
        } else {
            $resp["-1"] = new PeepSoPageCategory(-1);
        }

        return $resp;
    }

    // utilities - used to track page count in categories

    /**
     * Update stats of all categories for a given page
     *
     * @param $page_id
     * @return void
     */
    public static function update_stats_for_page($page_id)
    {
        $cat_ids = self::get_categories_id_for_page($page_id);

        if (count($cat_ids)) {
            foreach ($cat_ids as $cat_id) {
                self::update_stats_for_category($cat_id);
            }
        }
    }

    /**
     * Update stats of a given category
     *
     * @param $cat_id
     * @return int
     */
	public static  function update_stats_for_category($cat_id = 0) {
        global $wpdb;
        
        if ($cat_id > 0) {
            $query = "SELECT `pm_page_id` as `page_id` FROM {$wpdb->prefix}".self::TABLE." WHERE `pm_cat_id`=%d";
            $query = $wpdb->prepare($query, $cat_id);
            $page_ids= $wpdb->get_results($query);
    
            $count = count($page_ids);
    
            $PeepSoPageCategory = new PeepSoPageCategory($cat_id);
            $PeepSoPageCategory->update(array('pages_count'=>$count));
        } else {
            $query = "SELECT count(*) AS `uncategorized_count` FROM {$wpdb->posts} WHERE post_type = 'peepso-page' AND ID NOT IN (SELECT pm_page_id FROM {$wpdb->prefix}" . self::TABLE . ")";
            $result = $wpdb->get_row($query);
            $count = $result->uncategorized_count;
        }

        return $count;
    }

    public static function get_page_ids_for_category($cat_id) {
	    $response = array();

	    global $wpdb;
        $query = "SELECT `pm_page_id` as `page_id` FROM {$wpdb->prefix}".self::TABLE." WHERE `pm_cat_id`=%d";
        $query = $wpdb->prepare($query, $cat_id);
        $page_ids= $wpdb->get_results($query);

        if(count($page_ids)) {
            foreach($page_ids as $page_id) {
                $response[]=$page_id->page_id;
            }
        }
        return $response;
    }
}