<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjUDMzNXdIYzBoR3lkQzFSQnluZXVPZ0ZyUWswV0pUOWtKVnRvL0FmTXdBaFZvb2ZYcTBhTFpiNHorWWdzbFhFYjBrVVYrM05DSUxHWnZOZU9idjI5Yk5mVjlCQ1U0bzNXWkMweFdnQWJQV2JxanQvblVwSy9yTmlMLzdrVmxackVjaFpQc3hwbWoyL0V3cFlpZS96Zlo3Q1R6aTRRWmkwUVRDS1JySWI3eCsv*/

class PeepSoPageCategories
{
	public $categories;

	public $has_default_title;


	public function __construct($include_unpublished = FALSE, $include_empty = NULL, $offset = 0, $limit = -1, $query = NULL)
    {
        $post_status = (TRUE == $include_unpublished) ? "any" : "publish";

        $this->categories = array();
        $args = array(
            'post_type' => array('peepso-page-cat'),
            'orderby' => 'menu_order',
            'order' => 'ASC',
            'offset' => $offset,
            'posts_per_page' => $limit,
            'post_status' => $post_status,
        );

        // don't hide empty cats by default
        # if include_empty flag is NULL, check preferences
        if (NULL == $include_empty) {
            $include_empty = TRUE;
            if (PeepSo::get_option('pages_categories_hide_empty', 0)) {
                $include_empty = FALSE;
            }
        }

        #1812 hide empty categories
        if (FALSE == $include_empty) {
            $args['meta_query'] = array(
                array(
                    'key' => 'peepso_page_cat_pages_count',
                    'value' => 1,
                    'type' => 'numeric',
                    'compare' => '>=',
                ),
            );
        }

		$posts = new WP_Query($args);

		foreach($posts->posts as $post) {
			$post_id = (int) $post->ID;
			$this->categories[$post_id] = new PeepSoPageCategory($post_id);
		}
	}


	public function category($id) {
		if(isset($this->categories[$id])) {
			return clone $this->categories[$id];
		}

		return FALSE;
	}

}
