<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjUDMzNXdIYzBoR3lkQzFSQnluZXVPajRGNzhiV2hKWjJwWVNjdXpqbk5KM0llVDFCZ2NFVzhlaE1CQ0lhYXVoQnBKcm1NU3JaZ3RpSFkxOE9La2hYSHRTTzYwUVBOd0JpakN1d3VpVWNmS0Y4ZktYc0JGN0JHZjNGZ2FUbXBnV3gzN2Y2elJGb3Nka2F2eG9HVU5VaU5GOUlKZ2diYmFUNXFlUTBVdFBSSDMv*/

class PeepSoPagesListTable extends PeepSoListTable
{
	/**
	 * Defines the query to be used, performs sorting, filtering and calling of bulk actions.
	 * @return void
	 */
	public function prepare_items()
	{
		global $wpdb;
		$input = new PeepSoInput();
		if (isset($_POST['action'])){
			$this->process_bulk_action();
		}

		$limit = 20;
		$offset = ($this->get_pagenum() - 1) * $limit;

		$this->_column_headers = array(
			$this->get_columns(),
			array(),
			$this->get_sortable_columns()
		);

		$search = '';
		if (isset($_REQUEST['s'])) {
			$search = $_REQUEST['s'];
		}

		$show = 'all';
		if (isset($_REQUEST['show'])) {
			$show = $_REQUEST['show'];
		}

		$totalItems = PeepSoPages::admin_count_pages($search, $show);

		// SQL safe, admin only
		$aPages = PeepSoPages::admin_get_pages($offset, $limit, $input->value('orderby', NULL, FALSE), $input->value('order', 'desc',array('desc','asc')), $search, $show);

		$this->set_pagination_args(array(
				'total_items' => $totalItems,
				'per_page' => $limit
			)
		);
		$this->items = $aPages;
	}

	/**
	 * Return and define columns to be displayed on the List Pages table.
	 * @return array Associative array of columns with the database columns used as keys.
	 */
	public function get_columns()
	{
		return array(
			'cb' 					=> '<input type="checkbox" />',
			'page' 				=> __('Page', 'pageso'),
			'description' 			=> __('Description', 'pageso'),
			'categories'			=> __('Categories', 'pageso'),
			'admins' 				=> __('Owner', 'pageso'),
			'status'				=> __('Status', 'pageso'),
			'members_count'			=> __('Members', 'pageso')
		);
	}

	/**
	 * Return and define columns that may be sorted on the List Pages table.
	 * @return array Associative array of columns with the database columns used as keys.
	 */
	public function get_sortable_columns()
	{
		return array(
			'page' 				=> array('post_title', false),
			'description' 			=> array('post_content', false),
			'status' 				=> array('post_status', false),
			'members_count' 		=> array('members_count', false)
		);
	}

	/**
	 * Return default values to be used per column
	 * @param  array $item The post item.
	 * @param  string $column_name The column name, must be defined in get_columns().
	 * @return string The value to be displayed.
	 */
	public function column_default($item, $column_name)
	{
		//var_dump($item);
		return $item->$column_page;
	}

	/**
	 * Returns the HTML for the checkbox column.
	 * @param  array $item The current post item in the loop.
	 * @return string The checkbox cell's HTML.
	 */
	public function column_cb($item)
	{
		return sprintf('<input type="checkbox" name="pages[]" value="%d" />',
    		$item->id
    	);
	}

	/**
	 * Returns the HTML for the page description column.
	 * @param  array $item The current post item in the loop.
	 * @return string The name cell's HTML.
	 */
	public function column_description($item)
	{
		$page = new PeepSoPage($item->id);

		return sprintf('<div class="description-text">%s</div><a href="%s" target="_blank">%s <i class="fa fa-external-link"></i></a>',
    		$item->description,
    		$page->get_url(),
    		__('Read more', 'pageso')
    	);
	}

	/**
	 * Returns the Page name, avatar and privacy.
	 * @param  array $item The current post item in the loop.
	 * @return string The Avatar HTML.
	 */
	public function column_page($page)
	{
		return sprintf('<a href="%s" target="_blank">%s <i class="fa fa-external-link"></i></a><a href="%s" target="_blank"><img src="%s" class="ps-avatar"></a> <span>'
			. __('Privacy', 'pageso') . ': <i class="'.$page->privacy['icon'].'"></i> <strong>'.$page->privacy['name'] . '</strong></span><br>Page id: <code>'.$page->get('id').' </code></b>',
			$page->get_url(),
			$page->get('name'),
			$page->get_url(),
			$page->get_avatar_url_orig()
    	);
	}

	/**
	 * Returns the Owner for the owner column.
	 * @param  array $item The current post item in the loop.
	 * @return string The Page owner.
	 */
	public function column_admins($item)
	{
		$page_users = new PeepSoPageUsers($item->id);
		$list_owners = $page_users->get_owners();
		$owners = array();
		if(count($list_owners) > 0) {
			foreach($list_owners as $pageuser) {
				$owners[] = sprintf('<a href="%s" target="_blank">%s <i class="fa fa-external-link"></a>',
					$pageuser->get('profileurl'),
					$pageuser->get('fullname')
					);
			}
		}

		return implode(', ', $owners);
	}

	public function column_categories($page)
	{
		$categories = PeepSoPageCategoriesPages::get_categories_for_page($page->id);
		foreach($categories as $PeepSoCategory) {
			echo "{$PeepSoCategory->name}<br/>";
		}
	}

	/**
	 * Returns the Page Status for the status column.
	 * @param  array $item The current post item in the loop.
	 * @return string The Page status.
	 */
	public function column_status($item)
	{
		return ($item->published === TRUE) ? __('published', 'pageso') : __('unpublished', 'pageso');
	}

	/**
	 * Returns the HTML for the page name column.
	 * @param  array $item The current post item in the loop.
	 * @return string The name cell's HTML.
	 */
	public function column_members_count($item)
	{
		$page = new PeepSoPage($item->id);

		return sprintf('<a href="%s" target="_blank">%s <i class="fa fa-external-link"></i></a>',
			$page->get_url() . 'members',
    		$item->members_count
    	);
	}

	/**
	 * Define bulk actions available
	 * @return array Associative array of bulk actions, keys are used in self::process_bulk_action().
	 */
	public function get_bulk_actions()
	{
		return array(
			'publish' 	=> __('Publish', 'pageso'),
            'unpublish' => __('Unpublish', 'pageso'),
            'delete' => __('Delete', 'pageso'),
		);
	}

	/**
	 * Performs bulk actions based on $this->current_action()
	 * @return void Redirects to the current page.
	 */
	public function process_bulk_action()
	{
		if ($this->current_action() && check_admin_referer('bulk-action', 'pages-nonce')) {
			$input = new PeepSoInput();
			$count = 0;

			// SQL safe, forced int
			$posts = array_map('intval', $input->value('pages', array(), FALSE));

			$post = array();
			if ('unpublish' === $this->current_action() || 'publish' === $this->current_action()) {
				$notif = new PeepSoNotifications();

				foreach ($posts as $id) {
					$the_post = get_post($id);

					$post['ID'] = intval($id);
					$post['post_status'] = $this->current_action();

					wp_update_post($post);

					$user_id = get_current_user_id();

                    $args = ['pageso'];

					if('publish' === $this->current_action()) {
						if($this->current_action() !== $the_post->post_status){

                            $i18n = __('published your page', 'pageso');
                            $message = 'published your page';

							$notif->add_notification_new($user_id, $the_post->post_author, $message, $args, 'pages_publish', PeepSoPagesPlugin::MODULE_ID, $id);
						}
					} elseif('unpublish' === $this->current_action()) {
						if($this->current_action() !== $the_post->post_status){
                            $i18n = __('unpublished your page', 'pageso');
                            $message = 'unpublished your page';

							$notif->add_notification_new($user_id, $the_post->post_author, $message, $args, 'pages_unpublish', PeepSoPagesPlugin::MODULE_ID, $id);
						}
					}
				}

				$message = __('updated', 'pageso');
			}

			if('delete' === $this->current_action()) {

			    global $wpdb;

                foreach ($posts as $id) {
                    // Delete page uploads
                    $PeepSoPage = new PeepSoPage($id);
                    $dir = $PeepSoPage->get_image_dir();

                    require_once ABSPATH . '/wp-admin/includes/class-wp-filesystem-base.php';
                    require_once ABSPATH . '/wp-admin/includes/class-wp-filesystem-direct.php';
                    $filesystem = new WP_Filesystem_Direct(array());
                    $filesystem->rmdir($dir, TRUE);

                    wp_delete_post($id);

                    $message = __('deleted', 'pageso');
                }
            }

			$count = count($posts);

			PeepSoAdmin::get_instance()->add_notice(
				sprintf('%1$d %2$s %3$s',
					$count,
					_n('page', 'pages', $count, 'pageso'),
					$message),
				'note');

			ob_start();
            PeepSoMaintenancePages::deletePostsForDeletedPages();
            PeepSoMaintenancePages::deleteNotificationsForDeletedPages();
            $debug = ob_get_clean();

			PeepSo::redirect("//$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
		}
	}

	/**
	 * Adds The navigation to the top of the table.
	 * @param  string $which The current position to display the HTML.
	 * @return void Echoes the content.
	 */
	public function extra_tablenav($which)
	{
		if ('top' === $which) {
			$nonce = wp_create_nonce('process-pages-nonce');

			$show = (isset($_REQUEST['show'])) ? $_REQUEST['show'] : 'all';

			$link_all = '<a href="' . admin_url('admin.php?page=peepso-manage&tab=pages&show=all&_wpnonce=' . $nonce) . '">
					' . __('All', 'pageso') . '</a>';

			$link_published = '<a href="' . admin_url('admin.php?page=peepso-manage&tab=pages&show=publish&_wpnonce=' . $nonce) . '">
					' . __('Published', 'pageso') . '</a>';

			$link_unpublished = '<a href="' . admin_url('admin.php?page=peepso-manage&tab=pages&show=unpublish&_wpnonce=' . $nonce). '">' . __('Unpublished', 'pageso') . '</a>';

			switch ($show) {
				case 'publish':
					$link_published = '<strong>' . $link_published . '</strong>';
					break;

				case 'unpublish':
					$link_unpublished = '<strong>' . $link_unpublished . '</strong>';
					break;

				default:
					$link_all = '<strong>' . $link_all . '</strong>';
					break;
			}

			echo '
			<div class="alignleft actions filteractions">
				' . __('Show', 'pageso') . ' : ' .
				$link_all . ' | ' .
				$link_published . ' | ' .
				$link_unpublished .
			'</div>';
		}
	}
}

// EOF
