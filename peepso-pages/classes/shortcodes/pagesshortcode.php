<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjUDMzNXdIYzBoR3lkQzFSQnluZXVPakIxcHg4N1JpNzJkWC9vL29UWHIwZzdiTE1yUUFTejFER2djKzhHSENFaGNBL3UyWjh5dVIyNlRDSzgwZkJhQnA4TkhPM05pY2lwQXZHdWdQZzZDelhOMHFBZ01xbWtPN0JmRFY2UVlRbDFmYmNlWjcxTnA2dCtCdUREOHkzeDJyVlJGZkRWRks2OEZRY1dnSjd4ZTlj*/

class PeepSoPagesShortcode
{
	const SHORTCODE	= 'peepso_pages';

	private static $_instance 	= NULL;
	public $url 				= NULL;

    public $page				= NULL;
    public $page_id 			= NULL;
    public $page_user 			= NULL;
    public $page_segment_id 	= NULL;

    public $page_category				= NULL;
    public $page_category_id 			= NULL;
    public $page_category_segment_id 	= NULL;




    private function __construct()
    {
		add_filter('peepso_page_title', array(&$this,'peepso_page_title'));
		add_action('peepso_page_segment_settings', array(&$this, 'filter_page_segment_settings'));
		add_action('peepso_page_segment_members', array(&$this, 'filter_page_segment_members'));
		add_filter('peepso_page_title_check', array(&$this, 'peepso_page_title_check'));

		add_action('peepso_page_category_segment_pages', array(&$this, 'filter_page_category_segment_pages'));
	}

	public static function get_instance()
	{
		if (NULL === self::$_instance) {
			self::$_instance = new self();
		}
		return (self::$_instance);
	}


	public function set_page( $url )
	{
		if(!$url instanceof PeepSoUrlSegments) {
            return;
        }

        add_action('wp_enqueue_scripts', array(PeepSoPagesPlugin::get_instance(), 'enqueue_scripts'));
        $this->url = $url;

		$page_id = $url->get(1);
		// Attempting a single page view
		// A page ID can be numeric or a string (unique URL identifier)
		if( strlen($page_id) ) {

		    // Page Category view
		    if('category' == $page_id) {

                // Assume error: not found / unpublished / otherwise not accessible
                $this->page_category_id = FALSE;

                $this->page_category = new PeepSoPageCategory($url->get(2));

                // Page found
                if ($this->page_category->id) {
                    $this->page_category_id = $this->page_category->id;
                    $this->page_category_segment_id = $url->get(3);

                    // unregister "pages" listing
                    remove_shortcode(self::SHORTCODE);

                    // replace with "category" view
                    add_shortcode(self::SHORTCODE, array(self::get_instance(), 'shortcode_page_category'));
                } else {
                    // 404
                    $this->page_id = FALSE;
                }
            } else {
                // Page view

                // Assume error: not found / unpublished / otherwise not accessible
                $this->page_id = FALSE;

                $this->page = new PeepSoPage($page_id);

                // Page found
                if ($this->page->id) {
					status_header(200);

                    $this->page_id = $this->page->id;
                    $this->page_segment_id = $url->get(2);

                    $this->page_user = new PeepSoPageUser($this->page_id);

                    // Can current user access this page?
                    if ($this->page_user->can('access')) {

                        // unregister "pages" listing
                        remove_shortcode(self::SHORTCODE);
                        // replace with "page" view
                        add_shortcode(self::SHORTCODE, array(self::get_instance(), 'shortcode_page'));
                    } else {
                        // if user doesn't have an access just display pages not found
                        $this->page_id = FALSE;
                    }
                }
            }
		}
	}

    public function peepso_page_title( $title )
    {
        if(self::SHORTCODE == $title['title'] || $title['title'] == 'peepso_activity') {

			$title['newtitle'] = __('Pages', 'pageso');

			if( $this->page_id ) {
				$title['newtitle'] = $this->page->name;
			}
		}

        return $title;
    }


    public static function description() {
	    return __('Displays the Pages listing and single Page view.','pageso');
    }

    public static function post_state() {
        return _x('PeepSo', 'Page listing', 'pageso') . ' - ' . __('Pages', 'pageso');
    }

	/**
	 * Registers the callback function for the peepso_messages shortcode.
	 */
	public static function register_shortcodes()
	{
		add_shortcode(self::SHORTCODE, array(self::get_instance(), 'shortcode_pages'));
	}


    public function shortcode_page_category() {

        PeepSo::reset_query();

        $ret = PeepSoTemplate::get_before_markup();

        $args = array(
            'page_category' => $this->page_category,
            'page_category_segment' => $this->page_category_segment_id,
        );

        add_action('peepso_activity_dialogs', array(&$this, 'upload_dialogs_category'));

        if(strlen($this->page_category_segment_id)) {
            ob_start();
            do_action('peepso_page_category_segment_'.$this->page_category_segment_id, $args, $this->url);
            $ret .= ob_get_clean();
        } else {

            // activity filters & hooks
            add_filter('peepso_activity_meta_query_args', array(&$this, 'activity_meta_query_args'), 10, 2);

            $ret .= PeepSoTemplate::exec_template('pages', 'page-category', $args,TRUE);
        }

		// Page category page script.
		wp_enqueue_script('peepso-pages-page-category',
			PeepSo::get_asset('js/page-category.min.js', dirname(dirname(__FILE__))),
			array('jquery-ui-draggable', 'peepso', 'peepso-fileupload'), PeepSoPagesPlugin::PLUGIN_VERSION, TRUE);

		add_filter('peepso_data', function( $data ) {
			$category_data = array(
				'id'                  => $this->page_category->get('id'),
				'slug'                => $this->page_category->get('slug'),
				'name'                => $this->page_category->get('name'),
				'has_avatar'          => $this->page_category->has_avatar() ? TRUE : FALSE,
				'img_avatar'          => $this->page_category->get_avatar_url(),
				'img_avatar_default'  => $this->page_category->get_default_avatar_url(),
				'img_avatar_original' => $this->page_category->get_avatar_url_orig(),
				'avatar_nonce'        => wp_create_nonce('page-category-avatar'),
				'has_cover'           => $this->page_category->has_cover() ? TRUE : FALSE,
				'img_cover'           => $this->page_category->get_cover_url(),
				'img_cover_default'   => $this->page_category->get_cover_default(),
				'cover_nonce'         => wp_create_nonce('page-category-cover'),
				'text_error_filetype' => __('The file type you uploaded is not allowed. Only JPEG, PNG, and WEBP allowed.', 'pageso'),
				'text_error_filesize' => sprintf(
					__('The file size you uploaded is too big. The maximum file size is %s.', 'pageso'),
					'<strong>' . PeepSoGeneral::get_instance()->upload_size() . '</strong>'
				)
			);

			$data['page_category'] = array_merge(
				$category_data,
				array(
					'template_avatar'       => PeepSoTemplate::exec_template('pages', 'dialog-category-avatar', array( 'data' => $category_data ), TRUE),
					'template_cover_remove' => PeepSoTemplate::exec_template('pages', 'dialog-category-cover-remove', array(), TRUE),
				)
			);

			return $data;
		}, 10, 1);

		$has_avatar = $this->page_category->has_avatar() ? TRUE : FALSE;
		$avatar_url = $this->page_category->get_avatar_url();
		$avatar_url_default = $this->page_category->get_default_avatar_url();

		$has_cover = FALSE;
		$cover_url = $this->page_category->get_cover_url();
		if ( FALSE !== stripos($cover_url, 'peepso/page-categories/') ) {
			$has_cover = TRUE;
		}

		// Page category page data.
		wp_localize_script('peepso-pages-page-category', 'peepsoPageCategory', array(
			'id' => $this->page_category->get('id'),
			'name' => $this->page_category->get('name'),
			'avatar' => $avatar_url,
			'avatarUploadedImage' => $has_avatar ? $this->page_category->get_avatar_url_orig() : NULL,
			'avatarDefault' => $avatar_url_default,
			'cover' => $cover_url,
			'coverUploadedImage' => $has_cover ? $cover_url : NULL
		));

        $ret .= PeepSoTemplate::get_after_markup();

        return ($ret);
    }

	public function shortcode_page()
	{
		// Can current user access a particular page segment?
		if(!$this->page_user->can('access_segment', $this->page_segment_id)) {

			// Show content unavailable when guest visit any type of page
			if(!get_current_user_id()) {
				$ret = PeepSoTemplate::get_before_markup();
				$ret .= PeepSoTemplate::do_404();
				$ret .= PeepSoTemplate::get_after_markup();

				return ($ret);
			}

			// Force activity screen redirect if the segment is inaccessible
			PeepSo::redirect($this->page->get('url'));
			exit();
		}

		PeepSo::reset_query();

		$ret = PeepSoTemplate::get_before_markup();

		$args = array(
			'page' => $this->page,
			'page_segment' => $this->page_segment_id
		);

		if(!isset($this->url) || !($this->url instanceof PeepSoUrlSegments)) {
            $this->url = PeepSoUrlSegments::get_instance();
        }

        add_action('peepso_activity_dialogs', array(&$this, 'upload_dialogs'));

		if(strlen($this->page_segment_id)) {
			ob_start();
			do_action('peepso_page_segment_'.$this->page_segment_id, $args, $this->url);
			$ret .= ob_get_clean();
		} else {

			// activity filters & hooks
			add_filter('peepso_activity_meta_query_args', array(&$this, 'activity_meta_query_args'), 10, 2);

			$ret .= PeepSoTemplate::exec_template('pages', 'page', $args,TRUE);
		}


		$ret .= PeepSoTemplate::get_after_markup();

		return ($ret);
	}

	public function activity_meta_query_args($args, $module_id) {
		if($module_id === PeepSoPagesPlugin::MODULE_ID) {
			array_push($args['meta_query'],
				array(
					'compare' => '=',
					'key' => 'peepso_page_id',
					'value' => $this->page->id,
					)
				);
		}

		return $args;
	}

    public function filter_page_segment_settings($args)
    {
        $PeepSoUrlSegments = PeepSoUrlSegments::get_instance();
        if($tab = $PeepSoUrlSegments->get(3)) {
            PeepSoTemplate::exec_template('pages', 'page-settings-'.$tab, $args);
            return;
        }

        echo PeepSoTemplate::exec_template('pages', 'page-settings', $args);
    }

    public function filter_page_category_segment_pages($args)
    {
        echo PeepSoTemplate::exec_template('pages', 'page-category-pages', $args);
    }

	public function filter_page_segment_members($args)
	{

		if (!$this->page_user->can('view_users')) {
            return;
		}

		wp_enqueue_script('peepso-page-page-members',
			PeepSo::get_asset('js/page-page-members.js', dirname(dirname(__FILE__))),
			array('peepso', 'peepso-page-autoload'), PeepSo::PLUGIN_VERSION, TRUE);

		$PeepSoUrlSegments = PeepSoUrlSegments::get_instance();

		$tab = $PeepSoUrlSegments->get(3);

		if('pending' == $tab) {
			echo PeepSoTemplate::exec_template('pages', 'page-members-pending', $args);
			return;
		}

		if('invited' == $tab) {
			echo PeepSoTemplate::exec_template('pages', 'page-members-invited', $args);
			return;
		}

		if('banned' == $tab) {
			echo PeepSoTemplate::exec_template('pages', 'page-members-banned', $args);
			return;
		}

		if('management' == $tab) {
			echo PeepSoTemplate::exec_template('pages', 'page-members-management', $args);
			return;
		}

		echo PeepSoTemplate::exec_template('pages', 'page-members', $args);
	}

	public function shortcode_pages()
	{
		$allow_guest_access = PeepSo::get_option('pages_allow_guest_access_to_pages_listing', 0);

		PeepSo::reset_query();

		$ret = PeepSoTemplate::get_before_markup();

		// list / search pages
		$input = new PeepSoInput();
		$search = $input->value('filter', NULL, FALSE); // SQL Safe
		$category = $input->int('category', 0);

		$num_results = 0;

		// special case - 404, page hidden, you've been banned etc
		if( FALSE === $this->page_id ) {
			$ret .= PeepSoTemplate::do_404();
		} else {
			$ret .= PeepSoTemplate::exec_template('pages', 'pages', array('search' => $search, 'num_results' => $num_results, 'category' => $category, 'allow_guest_access' => $allow_guest_access), TRUE);
		}

		$ret .= PeepSoTemplate::get_after_markup();

		return ($ret);
	}

    /**
     * callback - peepso_activity_dialogs
     * Renders the dialog boxes for uploading profile and cover photo.
     */
    public function upload_dialogs()
    {
        wp_enqueue_script('jquery-ui-draggable');
        wp_enqueue_style('peepso-fileupload');
    }

    /**
     * callback - peepso_activity_dialogs
     * Renders the dialog boxes for uploading profile and cover photo.
     */
    public function upload_dialogs_category()
    {
        wp_enqueue_script('jquery-ui-draggable');
        wp_enqueue_style('peepso-fileupload');
    }

	/**
	 * todo:docblock
	 */
	public function peepso_page_title_check($post) {
		static $pages;

		$url = PeepSoUrlSegments::get_instance();
		$post_slug = $url->get(1);
		$page_id = '';

		if (!empty($post_slug) && is_null($pages)) {
			$args = array(
				'name'        => $post_slug,
				'post_type'   => PeepSoPage::POST_TYPE,
				'post_status' => 'publish',
				'numberposts' => 1
			);

			$pages = get_posts($args);
		}

		if (!empty($pages) && (count($pages) == 1) && empty($this->page_id)) {
			$this->page_id = $page_id = $pages[0]->ID;
			$this->page = new PeepSoPage($page_id);
		}

		if (((isset($post->post_content) && strpos($post->post_content, '[peepso_pages]') !== FALSE) && !empty($page_id)) && !is_front_page()) {
			return TRUE;
		}

		return $post;
	}

}

// EOF
