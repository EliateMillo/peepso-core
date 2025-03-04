<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjUDMzNXdIYzBoR3lkQzFSQnluZXVPaHUrMGZhV29QdGtaVWkvS1E3TS9BY2RLZFFVbGFWN1c2eVF4WEtPSFdVQTB0L0ZnbHFnT1Vqam1KaWU3dlVCZTFHZGM5OXdxdU1iMFF4UDVOOHhGcnd5VFRZbnpRbWFyeXdPbjRHSGFBN1JraFljQkNiVGVpNjZTeXh6ck1ORFFkcEJiQVdNZjcybmZUYWllMmdISWxr*/

class PeepSoConfigSectionPages extends PeepSoConfigSectionAbstract
{
// Builds the pages array
	public function register_config_groups()
	{
		$this->context='left';
        $this->_page_general();
        $this->new_members();
        $this->_page_listing();

		$this->context='right';
        $this->_page_categories();
        $this->_page_seo();
        $this->_page_permissions();

        if (class_exists('PeepSoFileUploads')) {
            $this->_page_files();
        }

        $this->_page_advanced();
	}

    private function _page_seo()
    {
        $this->args('default', 1);
        $this->args('descript', __('Enabled: /pages/my-amazing-page/','pageso') . '<br>'. __('Disabled: /pages/1234/','pageso'));
        $this->set_field(
            'pages_urls_slugs_enable',
            __('Use slugs in Page URLs', 'pageso'),
            'yesno_switch'
        );

        $options = array(
            0 => __('never','pageso'),
            1 => __('when Page name is changed','pageso'),
            2 => __('by the Page owner','pageso'),
        );
        $this->args('options', $options);
        $this->args('default', 'on');
        $this->args('descript', __('Option 1: Page slug will stay the same as the original Page name.','pageso') .'<br>'.__('Option 2: new Page slug will be generated upon Page name change.','pageso') . '<br>' . __('Option 3: the Page owner can change the slug manually.','pageso'));
        $this->set_field(
            'pages_slug_edit',
            __('Page slug changes', 'pageso'),
            'select'
        );

        $this->set_group(
            'opengraph',
            __('SEO', 'pageso')
        );
    }

    private function _page_listing(){
        # Show Page Owner on Pages listing
        $this->args('default', 1);
        $this->args('descript', __('Show or hide the Pages owner in Page listing','pageso'));
        $this->set_field(
            'pages_listing_show_page_owner',
            __('Show owner','pageso'),
            'yesno_switch'
        );

        # Show Page Creation date on Pages listing
        $this->args('default', 1);
        $this->args('descript', __('Show or hide the Pages creation dates in the Pages listing','pageso'));
        $this->set_field(
            'pages_listing_show_page_creation_date',
            __('Show creation date','pageso'),
            'yesno_switch'
        );

        # Allow guest access to Pages listing
        $this->args('descript', __('Show or hide the Pages listing from visitors who are not logged in','pageso'));
        $this->set_field(
            'pages_allow_guest_access_to_pages_listing',
            __('Allow guest access','pageso'),
            'yesno_switch'
        );

        $options = array(
            'id' => __('Recently added (default)','pageso'),
            'post_title' => __('Alphabetical','pageso'),
            'meta_members_count' => __('Followers count','pageso'),
        );
        $this->args('options', $options);

        $this->set_field(
            'pages_default_sorting',
            __('Default sort', 'pageso'),
            'select'
        );

        $options = array(
            'DESC' => __('Descending (default)','pageso'),
            'ASC' => __('Ascending','pageso'),
        );

        $this->args('options', $options);

        $this->set_field(
            'pages_default_sorting_order',
            __('Default sort direction', 'pageso'),
            'select'
        );

        // Single column view.
        /*
         * 0 = grid
         * 1 = list
         *
         * because "single column view"
         * 0 = no (grid)
         * 1 = yes (list(=)
         */
        $options = [
            0 => __('Grid', 'pageso'),
            1 => __('List', 'pageso'),
        ];
        $this->args('options', $options);
        $this->args( 'descript', __( 'Controls the default view. If user changes the view, their preference becomes "sticky"', 'pageso' ) );
        $this->set_field(
            'pages_single_column',
            __( 'Default list style', 'pageso' ),
            'select'
        );

        $this->set_group(
            'opengraph',
            __('Page listings', 'pageso')
        );
    }

	private function _page_general()
	{
        # Enable Page Creation
        $this->args('default', 1);
        $this->args('descript', __('Enabled: all site members can create pages','pageso') .'<br>' .__('Disabled: only site admins can create pages','pageso'));
        $this->set_field(
            'pages_creation_enabled',
            __('Enable Page creation', 'pageso'),
            'yesno_switch'
        );

        # Page Rules
//        $this->args('descript', __('Enabled: page Owners and Managers will be able to define dedicated page rules','pageso'));
//        $this->set_field(
//            'pages_rules_enabled',
//            __('Enable Page rules', 'pageso'),
//            'yesno_switch'
//        );


		$general_config = apply_filters('peepso_pages_general_config', array());

		if(count($general_config) > 0 ) {

			foreach ($general_config as $option) {
				if(isset($option['descript'])) {
					$this->args('descript', $option['descript']);
				}
				if(isset($option['int'])) {
					$this->args('int', $option['int']);
				}
				if(isset($option['default'])) {
					$this->args('default', $option['default']);
				}

				$this->set_field($option['name'], $option['label'], $option['type']);
			}
		}

        // Build Page
		$this->set_group(
			'general',
			__('General', 'pageso')
		);
	}

	private function new_members() {

        $this->set_field(
            'pages_post_to_stream_separator',
            __('Post to stream when liked', 'pageso'),
            'separator'
        );

        # Post to stream when liked
        $this->args('descript', __('Enabled: automatically post on user\'s stream when they like a page. These posts are deleted automatically when the user leaves or is removed/banned from the page.','pageso'));
        $this->set_field(
            'pages_join_post_to_stream',
            __('Enabled','pageso'),
            'yesno_switch'
        );

        $this->args('descript', __('For example "liked this page". Leave empty for default. Applies to old posts too.','pageso'));
        $this->set_field(
            'pages_join_post_action_text_page',
            __('Action text (on Page stream)','pageso'),
            'text'
        );

        $this->args('descript', __('For example "liked a page:". Leave empty for default. Applies to old posts too.','pageso'));
        $this->set_field(
            'pages_join_post_action_text_other',
            __('Action text (on other streams)','pageso'),
            'text'
        );

        $this->set_field(
          'pages_notifications_separator',
          __('Notifications', 'pageso'),
          'separator'
        );

        # Default onsite subscription status
        $this->args('default', 1);
        $this->args('descript', __('Enabled: new Page followers will automatically be subscribed to receive new posts notifications (on-site)','pageso'));
        $this->set_field(
            'pages_notify_default',
            __('Automatically subscribe new followers to notifications', 'pageso'),
            'yesno_switch'
        );

        # Default email subscription status
        $this->args('default', 1);
        $this->args('descript', __('Enabled: new Page followers will automatically be subscribed to receive new posts email notifications','pageso'));
        $this->set_field(
            'pages_notify_email_default',
            __('Automatically subscribe new followers to emails', 'pageso'),
            'yesno_switch'
        );

        // Build Page
        $this->set_group(
            'newmembers',
            __('New members', 'pageso')
        );
    }

	private function _page_categories()
	{
		# Enable Page Categories
		$this->args('default', 0);
		$this->args('descript', __('Users will be able to assign Pages to categories.','pageso'));
		$this->set_field(
			'pages_categories_enabled',
			__('Enable Page categories', 'pageso'),
			'yesno_switch'
		);

        # Set Page Categories as default view
        $this->args('default', 0);
        $this->set_field(
            'pages_categories_default_view',
            __('Set Page categories as default view.', 'pageso'),
            'yesno_switch'
        );

        # Enable Multiple Categories Per Page
        if(isset($_GET['5814'])) {
            $this->args('default', 0);
            $this->args('descript', __('Users will be able to assign a Page to multiple categories', 'pageso'));
            $this->set_field(
                'pages_categories_multiple_enabled',
                __('Allow multiple categories per Page', 'pageso'),
                'yesno_switch'
            );
         }

        #5814 this used to be pages_categories_multiple_enabled
        $options = [];
        for($i = 1; $i<=100; $i++) {
            $options[$i] = $i;
        }
        $this->args('options', $options);
        $this->set_field(
            'pages_categories_multiple_max',
            __('Allowed categories per Page', 'pageso'),
            'select'
        );


        # Categories per ajax call
        $this->args('default', 1);

        $options = array();
        for ($i = 1; $i <= 10; $i++) {
            $options[$i] = sprintf(_n('%d category', '%d categories', $i, 'pageso'), $i);
        }

        $options[1] .=' ('.__('default', 'pageso').')';
        $this->args('options', $options);

        $this->args('descript', __('Only this many categories will show in each ajax call in category listing.','pageso'));

        $this->set_field(
            'pages_categories_count',
            __('Each ajax call loads', 'pageso'),
            'select'
        );

        $this->set_field(
            'pages_category_list',
            __('Category listing', 'pageso'),

            'separator'
        );

        # Pages per category
        $this->args('default', 4);

        $ints = array(2,4,6,8,10,12,14,16,18,20,22,24,26,28,30);

        $options = array();

        foreach($ints as $i) {
            $options[$i] = sprintf(__('%d pages','pageso'), $i);
        }

        $options[4].=' ('.__('default', 'pageso').')';
        $this->args('options', $options);




        $this->args('descript', __('Only this many Pages will show in each category before offering a link to the full category listing.','pageso'));

        $this->set_field(
            'pages_categories_page_count',
            __('Each category loads', 'pageso'),
            'select'
        );


        # Expand All Cateogires
        $this->args('descript', __('Disabled: only the first category shows a preview of the Page listing.','pageso'));
        $this->set_field(
            'pages_categories_expand_all',
            __('Expand all categories', 'pageso'),
            'yesno_switch'
        );

        # Hide Empty Categories
        $this->args('default', 0);
        $this->args('descript', __('Hide categories which don\'t have any Pages assigned to them.','pageso'));
        $this->set_field(
            'pages_categories_hide_empty',
            __('Hide empty categories', 'pageso'),
            'yesno_switch'
        );

        # Show Page Count
        $this->args('default', 0);
        $this->args('descript', __('The count will not be accurate if a category contains unpublished or secret pages.','pageso'));
        $this->set_field(
            'pages_categories_show_count',
            __('Show Page count', 'pageso'),
            'yesno_switch'
        );

		// Build Page
		$this->set_group(
			'categories',
			__('Categories', 'pageso')
		);
	}

    private function _page_permissions()
    {

        $this->args('descript', __('Enabled: Page owners can edit posts and comments in respective pages', 'pageso') . '<br/>' . __('Disabled: only admin can edit posts and comments', 'pageso'));
        $this->args('default', 1);
        $this->set_field(
            'pages_post_edits_owner',
            __('By Page owners', 'pageso'),
            'yesno_switch'
        );

        $this->args('descript', __('Enabled: Page managers can edit posts and comments in respective pages', 'pageso') . '<br/>' . __('Disabled: only admin can edit posts and comments', 'pageso'));
        $this->args('default', 1);
        $this->set_field(
            'pages_post_edits_manager',
            __('By Page managers', 'pageso'),
            'yesno_switch'
        );

        $this->args('descript', __('Enabled: Page moderators can edit posts and comments in respective pages', 'pageso') . '<br/>' . __('Disabled: only admin can edit posts and comments', 'pageso'));
        $this->args('default', 1);
        $this->set_field(
            'pages_post_edits_moderator',
            __('By Page moderators', 'pageso'),
            'yesno_switch'
        );

        // Build Page
        $this->set_group(
            'permissions',
            __('Editing Page posts and comments', 'pageso')
        );
    }

    private function _page_files()
    {

        $this->args('descript', __('Enabled: Page owners can moderate files in their pages', 'pageso') . '<br/>' . __('Disabled: only admin can moderate files', 'pageso'));
        $this->args('default', 1);
        $this->set_field(
            'pages_file_edits_owner',
            __('By Page owners', 'pageso'),
            'yesno_switch'
        );

        $this->args('descript', __('Enabled: Page managers can moderate files in their pages', 'pageso') . '<br/>' . __('Disabled: only admin can moderate files', 'pageso'));
        $this->args('default', 1);
        $this->set_field(
            'pages_file_edits_manager',
            __('By Page managers', 'pageso'),
            'yesno_switch'
        );

        $this->args('descript', __('Enabled: Page moderators can moderate files in their pages', 'pageso') . '<br/>' . __('Disabled: only admin can moderate files', 'pageso'));
        $this->args('default', 1);
        $this->set_field(
            'pages_file_edits_moderator',
            __('By Page moderators', 'pageso'),
            'yesno_switch'
        );

        // Build Page
        $this->set_group(
            'permissions',
            __('Editing Page files', 'pageso')
        );
    }

    private function _page_advanced()
    {
        if(PeepSo::get_option_new('pinned_posts_enable')) {
            # Don't pin on main stream
            $this->args('descript', __('Enabled: pinned Page posts will be pinned to top only in Page views', 'pageso') . '<br/>' . __('Disabled: pinned Page posts will be also pinned to top on author profile and main activity', 'pageso'));
            $this->set_field(
                'pages_pin_page_only',
                __('Pin to top only inside Pages', 'pageso'),
                'yesno_switch'
            );

            $this->args('descript', __('Enabled: pinned Page posts will only have a "pinned" style when viewed in the Page context and single post view', 'pageso') . '<br/>' . __('Disabled: pinned page posts will always have the "pinned" style', 'pageso'));
            $this->set_field(
                'pages_pin_page_only_no_pinned_style',
                __('Use "pinned" style only in Page context', 'pageso'),
                'yesno_switch'
            );

            $this->args('descript', __('Enabled: Page management will be allowed to pin posts.', 'pageso') . '<br/>' . __('Disabled: only comunity/site administrators will be allowed to pin posts', 'pageso'));
            $this->set_field(
                'pages_pin_allow_managers',
                __('Allow Page managers to pin posts', 'pageso'),
                'yesno_switch'
            );
        }

        $this->args('descript', __('Enabled: PeepSo will disallow creating Pages with the same names. Runs a risk of exposing the existence of secret Pages to non-members.','pageso'));
        $this->set_field(
            'pages_unique_names',
            __('Force unique names', 'pageso'),
            'yesno_switch'
        );


        # Replace "invite" with "add" for admins
        // $this->args('default', 0);
        // $this->args('descript', __('Site and Community Administrators will add users to Page without the need to confirm.','pageso'));
        // $this->set_field(
        //     'pages_add_by_admin_directly',
        //     __('Replace "invite" with "add" for Admins', 'pageso'),
        //     'yesno_switch'
        // );

        # Email admins about new pages
        $this->args('default', 0);
        $this->args('descript', __('Users with Administrator role will receive an email when a new Page is created','pageso'));
        $this->set_field(
            'pages_create_notify_admin',
            __('Email Admins when a new Page is created', 'pageso'),
            'yesno_switch'
        );

        # Page Meta in Stream items
        // $this->args('default', 0);
        // $this->args('descript', __('When enabled, hovering over Page names on stream will display membership and following summary. This feature is likely to slow down stream performance.','pageso'));
        // $this->set_field(
        //     'pages_meta_in_stream',
        //     __('Show membership summary for Page names on stream', 'pageso'),
        //     'yesno_switch'
        // );

        $this->set_field(
            'pages_members_tab_sep',
            __('Followers tab', 'pageso'),
            'separator'
        );


        $this->args('descript', __('Has no effect on Owner, Managers and Site Administrators','pageso'));
        $this->set_field(
            'pages_members_tab',
            __('Enabled', 'pageso'),
            'yesno_switch'
        );

        $this->set_field(
            'pages_members_tab_override',
            __('Allow pages to override this setting', 'pageso'),
            'yesno_switch'
        );

        // Build Page
        $this->set_group(
            'advanced',
            __('Advanced', 'pageso')
        );
    }

}