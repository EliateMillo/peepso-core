<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjUDMzNXdIYzBoR3lkQzFSQnluZXVPaHUzR0k0OVlEU281SEZmdGJkajI1emN4QVd1RHNqQi9aVUtOdGFsdTI4L0dKZTV6cHRxdU9aaHhXVlJxenZjdDI0L1dVUE94TVZwbHNHb256azVFanBGYUNCUUhRWTZvRHh6QTFtVEt0OVptbW5ab0JHWU9kRXRwZkZiSFdrZXRLKzFtL3E4cE9aN0RxVWs3eVR3LytW*/

class PeepSoGroupCategories
{
	public $categories;

	public $has_default_title;


	public function __construct($include_unpublished = FALSE, $include_empty = NULL, $offset = 0, $limit = -1, $query = NULL)
    {
        $post_status = (TRUE == $include_unpublished) ? "any" : "publish";

        $this->categories = array();
        $args = array(
            'post_type' => array('peepso-group-cat'),
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
            if (PeepSo::get_option('groups_categories_hide_empty', 0)) {
                $include_empty = FALSE;
            }
        }

        #1812 hide empty categories
        if (FALSE == $include_empty) {
            $args['meta_query'] = array(
                array(
                    'key' => 'peepso_group_cat_groups_count',
                    'value' => 1,
                    'type' => 'numeric',
                    'compare' => '>=',
                ),
            );
        }

		$posts = new WP_Query($args);

		foreach($posts->posts as $post) {
			$post_id = (int) $post->ID;
			$this->categories[$post_id] = new PeepSoGroupCategory($post_id);
		}
	}


	public function category($id) {
		if(isset($this->categories[$id])) {
			return clone $this->categories[$id];
		}

		return FALSE;
	}

}
