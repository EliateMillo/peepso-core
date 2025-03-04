<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjUDMzNXdIYzBoR3lkQzFSQnluZXVPZ3BkK0s3b3MvNk9TVGdhQ0IxL2dQZHRpKzNTTWNVRVlPcFNPczZpVHU3MFlLTGdqcHBmc0NFcS8vRC8zeHlHSWpyTVZxa21udkF5eTdSaUcyOUVWMHMwMXVlS0pieEtuSDRsRTVCNnhTb2RkQnRSbGxkVmVSRmFsQjRnSUNUdXRzPQ==*/
/**
 * Plugin Name: PeepSo Core: Pages (BETA)
 * Plugin URI: https://PeepSo.com
 * Description: Pages are used to represent businesses, causes, celebrities or brands to connect with their audience, share content, and engage with followers.
 * Author: PeepSo
 * Author URI: https://PeepSo.com
 * Version: 7.0.3.0
 * Copyright: (c) PeepSo, Inc. All Rights Reserved.
 * License: GPLv2 or later (PHP), property of PeepSo, Inc. (CSS, JavaScript, images)
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: pageso
 * Domain Path: /language
 *
 * The PHP code of this plugin is Open Source. This PHP software can be redistributed and/or modified under the terms of the
 * GNU General Public License (version 2 or later), as published by the Free Software Foundation.
 * See the GNU General Public License or the LICENSE file for more details.
 *
 * Unless explicitly stated otherwise inside the respective files, all CSS, JavaScript, and images included in this plugin are proprietary to PeepSo, Inc.
 * They are protected by copyright and other applicable intellectual property laws.
 * Unauthorized use, reproduction, or distribution of these assets is STRICTLY PROHIBITED.
 *
 * "PeepSo" and "Your Community. Your Way." are registered trademarks of PeepSo, Inc.
 *
 * This software is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY.
 */

class PeepSoPagesPlugin
{
    private static $_instance = NULL;

    private $url_segments;
    private $input;

    private $photo_page_system_album;

    const PLUGIN_NAME	 = 'Core: Pages';
    const PLUGIN_VERSION = '7.0.3.0';
    const PLUGIN_RELEASE = ''; //ALPHA1, RC1 etc, '' for STABLE
    const PLUGIN_EDD = 107437567;
    const PLUGIN_SLUG 	 = 'pages';

    const JOIN_STREAM = 'PEEPSO_GROUPS_JOIN_STREAM_NOTIFICATION';
    const JOIN_STREAM_META = 'peepso_pages_is_join_notification';

    const MODULE_ID 	 = 9;

    const ICON_CATEGORIES = 'https://cdn.peepso.com/icons/plugins/107437567.svg';

    public $shortcodes= array(
        'peepso_pages' => 'PeepSoPagesShortcode::shortcode_pages',
    );

    public $view_user_id;
    public $file_avatar;
    public $file_cover;

    public $widgets = array(
        // 'PeepSoWidgetPage',
        // 'PeepSoWidgetPopularPosts'
    );

    public static $page_slug_blocklist = array('category',);

    private static function ready() {
        if (class_exists('PeepSo') && PeepSo::ready(self::PLUGIN_VERSION)) {
            return TRUE;
        }
    }

    private function __construct()
    {
        /** VERSION INDEPENDENT hooks **/

        // Admin
        add_filter('peepso_license_config', function($list) {
            $data = array(
                'plugin_slug' => self::PLUGIN_SLUG,
                'plugin_name' => self::PLUGIN_NAME,
                'plugin_edd' => self::PLUGIN_EDD,
                'plugin_version' => self::PLUGIN_VERSION
            );
            $list[] = $data;
            return ($list);
        });

        if (is_admin()) {
            add_action('admin_init', array(&$this, 'peepso_check'));
        }

        // Compatibility
        add_filter('peepso_all_plugins', function($plugins){
            $plugins[plugin_basename(__FILE__)] = get_class($this);
            return $plugins;
        });

        // Translations
        add_action('init', function(){
            $path = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'language' . DIRECTORY_SEPARATOR . 'pageso-' . determine_locale() . '.mo';
            load_textdomain('pageso', $path);
        });

        // Activation
        register_activation_hook(__FILE__, array(&$this, 'activate'));

        /** VERSION LOCKED hooks **/
        if(self::ready()) {
            if (!PeepSoLicense::check_license(self::PLUGIN_EDD, self::PLUGIN_SLUG)) {
                return;
            }

            if(is_admin()) {
                add_filter('peepso_report_column_title', function($title, $item, $column_name) {

                    if ('post_title' === $column_name) {
                        if (PeepSoPagesPlugin::MODULE_ID === intval($item['rep_module_id'])) {
                            return ('<a href="' . PeepSo::get_page('activity_status') . $item['post_title'] . '/" target="_blank">' . $item['post_title'] . ' <i class="fa fa-external-link"></i></a>');
                        }
                    }
                    return ($title);
                }, 20, 3);
            }

            add_filter('peepso_filter_shortcodes', function ($list) {
                return array_merge($list, $this->shortcodes);
            });

            add_action('peepso_init', array(&$this, 'init'));

            // Owner, Manager and Moderator should be able to delete, edit  and pin/unpin posts
            add_filter('peepso_check_permissions-comment_delete', array(&$this, 'check_permissions_delete_content'), 99, 4);
            add_filter('peepso_check_permissions-post_delete', array(&$this, 'check_permissions_delete_content'), 99, 4);
            add_filter('peepso_check_permissions-post_edit', array(&$this, 'check_permissions_edit_content'), 99, 4);
            add_filter('peepso_can_pin', function ($can_pin, $post_id) {

                if(!PeepSo::get_option_new('pages_pin_allow_managers')) {
                    // echo "Pages - pins not allowed";
                    return FALSE;
                }

                // If post ID is null, we are checking postbox permission
                // Extract page id from URL and decide
                if($post_id == NULL) {
                    $page_id = NULL;
                    if (PeepSoUrlSegments::get_instance()->_shortcode == 'peepso_pages') {

                        $sc = PeepSoPagesShortcode::get_instance();
                        $page_id = $sc->page_id;
                    }
                } else {
                    $page_id = get_post_meta($post_id, 'peepso_page_id', true);
                }

                if (is_numeric($page_id)) {
                    $PeepSoPageUser = new PeepSoPageUser($page_id);
                    $can_pin = $PeepSoPageUser->can('manage_content');
                }

                return $can_pin;
            }, 10, 2);

            add_filter('peepso_can_nsfw', function ($can_nsfw, $post_id) {
                $page_id = get_post_meta($post_id, 'peepso_page_id', true);

                if (!empty($page_id)) {
                    $PeepSoPageUser = new PeepSoPageUser($page_id);
                    $can_nsfw = $PeepSoPageUser->can('manage_content');
                }

                return $can_nsfw;
            }, 10, 2);

            // Clean up "joined a page" notification posts
            add_filter('peepso_activity_content', function ($content) {
                global $post;
                if (self::is_join_notification($post)) {
                    $content = '';
                }

                return $content;
            }, -1, 1);

            #5814 migrate pages_categories_multiple_enabled to pages_categories_multiple_max
            add_action('init', function() {
                if(get_option('peepso_5814_migrated')) { return; }

                if(PeepSo::get_option('pages_categories_multiple_enabled',0)) {
                    $settings = PeepSoConfigSettings::get_instance();
                    $settings->set_option('pages_categories_multiple_max', 100);
                }

                update_option('peepso_5814_migrated', 1);
            });
            // add_filter('peepso_widgets', array(&$this, 'register_widgets'));
        }
    }


    public function filter_check_query($sc, $page, $url)
    {
        if(PeepSoPagesShortcode::SHORTCODE == $page ) {
            $sc = PeepSoPagesShortcode::get_instance();
            $sc->set_page($url);
        }
    }

    public static function get_instance()
    {
        if (NULL === self::$_instance) {
            self::$_instance = new self();
        }
        return (self::$_instance);
    }


    public function init()
    {
        // Load classes, templates and shortcoded only in backend, or in frontend
        $dir_classes = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR;
        PeepSo::add_autoload_directory($dir_classes);

        PeepSoTemplate::add_template_directory(plugin_dir_path(__FILE__));

        PeepSoPagesShortCode::register_shortcodes();

        add_filter('peepso_activity_remove_shortcode', array(&$this, 'filter_activity_remove_shortcode'));

        // #5554 & #5556 load this always, to fix WP 5.8 widget preview
        add_filter('peepso_photos_post_clauses',		array(&$this, 'filter_photos_post_clauses'), 10, 3);
        add_filter('peepso_videos_post_clauses',		array(&$this, 'filter_videos_post_clauses'), 10, 3);

        if (is_admin()) {
            add_action('admin_init', 						array(&$this, 'peepso_check'));
            add_action('admin_enqueue_scripts', array(&$this, 'admin_enqueue_scripts'));
            add_filter('peepso_admin_config_tabs', 			function($tabs){
                $tabs['pages'] = array(
                    'label' => __('Pages', 'pageso'),
                    'icon' => 'https://cdn.peepso.com/icons/plugins/'.self::PLUGIN_EDD.'.svg',
                    'tab' => 'pages',
                    'description' => __('PeepSo Pages', 'pageso'),
                    'function' => 'PeepSoConfigSectionPages',
                    'cat' => 'core',
                );

                return $tabs;
            });

            add_filter('peepso_admin_manage_tabs', function($tabs){
                $tabs['pages'] = array(
                    'label' => __('Pages', 'pageso'),
                    'icon' => 'https://cdn.peepso.com/icons/plugins/'.self::PLUGIN_EDD.'.svg',
                    'tab' => 'pages',
                    'description' => '',
                    'function' => array('PeepSopagesAdmin', 'admin_page'),
                    'cat'   => 'core',
                );

                $tabs['page_categories'] = array(
                    'label' => __('Page categories', 'pageso'),
                    'icon' => self::ICON_CATEGORIES,
                    'tab' => 'page_categories',
                    'description' => '',
                    'function' => array('PeepSoPageCategoriesAdmin', 'administration'),
                    'cat'   => 'core',
                );

                return $tabs;
            });

        } else {
            add_action('peepso_action_post_classes', function($id) {
                global $post;
                if (get_post_meta($post->ID, 'peepso_page_id', true)) {
                    echo " ps-post--page";
                }
            });

            add_action('wp_enqueue_scripts', array(&$this, 'enqueue_scripts'));

            // hide privacy in postbox
            add_filter('peepso_postbox_interactions', 		array(&$this, 'postbox_interactions'), 20, 2);
            add_filter('peepso_postbox_options', 			array(&$this, 'filter_postbox_options'), 20, 2);
            add_filter('peepso_postbox_options_post_to_options', array(&$this, 'filter_postbox_options_post_to_options'), 20);

            // show/hide commentsbox
            add_filter('peepso_commentsbox_display',		array(&$this, 'commentsbox_display'), 10, 2);

            // Profile Segments
            add_action('peepso_profile_segment_pages', 	array(&$this, 'filter_profile_segment_pages'));
            add_filter('peepso_widget_me_community_links', 			array(&$this, 'filter_widget_me_community_links'));
            add_filter('peepso_rewrite_profile_pages', 		array(&$this, 'filter_rewrite_profile_pages'));

            // activity filters & hooks
            add_filter('peepso_post_filters', 				array(&$this, 'post_filters'), -1,1);
            add_filter('peepso_post_filters', 				array(&$this, 'post_filters_after'), 50);
            add_filter('peepso_activity_post_actions', 		array(&$this, 'modify_post_actions'),50); // priority set to last
            add_filter('peepso_activity_comment_actions', 	array(&$this, 'modify_comments_actions'),50); // priority set to last
            add_filter('peepso_activity_post_clauses', 		array(&$this, 'filter_post_clauses'), 10, 2);
            add_filter('peepso_activity_meta_query_args', 	array(&$this, 'activity_meta_query_args'), 10, 2);

            // ajax auth exceptions
            add_filter('peepso_photos_ajax_auth_exceptions', function($exceptions){
                if (isset($_GET['page_id']) &&
                    (isset($_GET['module_id']) && $_GET['module_id'] == PeepSoPagesPlugin::MODULE_ID)
                ) {
                    $exceptions = array_merge($exceptions, array('get_user_photos', 'get_list_albums', 'get_user_photos_album'));
                }

                return $exceptions;
            });

            add_filter('peepso_videos_ajax_auth_exceptions', function($exceptions){
                if (isset($_GET['page_id']) &&
                    (isset($_GET['module_id']) && $_GET['module_id'] == PeepSoPagesPlugin::MODULE_ID)
                ) {
                    $exceptions = array_merge($exceptions, array('get_user_videos'));
                }

                return $exceptions;
            });


            add_filter('peepso_activity_post_clauses_follow', function($following){
                global $wpdb;

                if (isset($GLOBALS['peepso_page_only'])) {
                    $following['pages'] = "(`peepso_page_meta`.`meta_value` IS NOT NULL)";
                } else {
                    $following['pages'] = "(`peepso_page_meta`.`meta_value` IS NOT NULL AND peepso_page_member.pm_page_id IN (SELECT pf_page_id FROM " . $wpdb->prefix . PeepSoPageFollowers::TABLE . " WHERE pf_user_id = " . get_current_user_id() . " AND pf_follow=1))";
                }

                return $following;
            });

            add_filter('posts_clauses_request', function($clauses, $query) {
                if (isset($GLOBALS['peepso_page_only'])) {
                    global $wpdb;
                    $clauses['join'] .= " LEFT JOIN `{$wpdb->prefix}" . PeepSoActivityRanking::TABLE . "` `activity_ranking` ON `activity_ranking`.`rank_act_id` = `act`.`act_id`";

                    $clauses['where'] .= $wpdb->prepare(" AND {$wpdb->posts}.post_date >= %s ", date('Y-m-d H:i:s', strtotime('-100 days')));

                    $clauses['orderby'] = "`activity_ranking`.`rank_act_score` DESC";
                }

                return $clauses;
            }, 99, 2);

            add_filter('peepso_activity_post_actions', function($actions) {
                if (isset($GLOBALS['peepso_remove_post_actions'])) {
                    unset($actions['acts']['follow'], $actions['acts']['save'], $actions['acts']['like']['click']);

                    $actions['acts']['like']['class'] = str_replace('ps-js-reaction-toggle', '', $actions['acts']['like']['class']);
                }
				return $actions;
			});

            add_action('peepso_activity_after_add_post', 	array(&$this, 'after_add_post'), 10, 2);
            add_action('peepso_after_add_comment', array($this, 'after_add_comment'), 10, 4);
            add_action('future_to_publish',                 array(&$this, 'future_to_publish'),10,1);
            add_filter('peepso_activity_allow_empty_content', array(&$this, 'activity_allow_empty_content'), 10, 1);

            add_filter('peepso_postbox_author_avatar', 		array(&$this, 'filter_postbox_author_avatar'), 10, 1);
            add_filter('peepso_postbox_author_fullname', 		array(&$this, 'filter_postbox_author_fullname'), 10, 1);
            add_filter('peepso_activity_stream_author_avatar', 		array(&$this, 'filter_activity_stream_author'), 10, 5);
            add_filter('peepso_activity_stream_comments_author_avatar', 		array(&$this, 'filter_activity_stream_comments_author_avatar'), 10, 4);
            add_filter('peepso_activity_stream_comments_author_name', 		array(&$this, 'filter_activity_stream_comments_author_name'), 10, 4);
            add_filter('peepso_activity_commentsbox_author_avatar', 		array(&$this, 'filter_activity_commentsbox_author_avatar'), 10, 4);
            add_filter('peepso_mention_info',               array(&$this, 'filter_mention_info'), 10, 2);
            add_filter('peepso_notification_avatar', 		array(&$this, 'filter_notification_avatar'), 10, 3);
            add_filter('peepso_notification_user_firstname', 		array(&$this, 'filter_notification_user_firstname'), 10, 3);
            add_filter('peepso_activity_stream_title', 		array(&$this, 'filter_activity_stream_title'), 10, 3);
            add_filter('peepso_activity_has_privacy', 		array(&$this, 'filter_activity_has_privacy'), 10, 2);
            add_filter('peepso_photos_dir_' . self::MODULE_ID,		array(&$this, 'photos_pages_dir'));
            add_filter('peepso_photos_url_' . self::MODULE_ID,		array(&$this, 'photos_pages_url'));
            add_filter('peepso_photos_thumbs_url_' . self::MODULE_ID,		array(&$this, 'photos_pages_thumbs_url'), 10, 2);
            add_filter('peepso_post_photos_location',		array(&$this, 'post_photos_pages_location'), 10, 3);
            add_filter('peepso_post_photos_ajax_id',		function($id, $post_id) {
                $page_id = get_post_meta($post_id, 'peepso_page_id', true);

                if(!empty($page_id)) {
                    $id=$page_id;
                }

                return $id;
            },10,2);

            add_filter('peepso_post_photos_ajax_dir',		function($dir, $post_id) {
                $page_id = get_post_meta($post_id, 'peepso_page_id', true);

                if(!empty($page_id)) {
                    $dir='pages';
                }

                return $dir;
            },10,2);

            // opengraph
            add_filter('peepso_filter_opengraph_tags', array(&$this, 'peepso_filter_opengraph_tags'), 10, 2);

            // single activity view accessible
            add_filter('peepso_access_content',  array(&$this, 'access_content'), 10, 3);

            // hide pages photos from photos widgets
            add_filter('peepso_photos_photo_click',			array(&$this, 'filter_photos_photo_click'), 10, 3);
            add_filter('peepso_photos_photo_item_click',	array(&$this, 'filter_photos_photo_item_click'), 10, 3);
            add_filter('peepso_photos_set_as_avatar',		array(&$this, 'filter_photos_photo_set_as_avatar'), 10, 3);
            add_filter('peepso_photos_set_as_cover',		array(&$this, 'filter_photos_photo_set_as_cover'), 10, 3);

            add_filter('peepso_photos_filter_owner_album',	array(&$this, 'filter_photos_owner_album'));
            add_filter('peepso_photos_album_owner_profile_url',				array(&$this, 'filter_photos_owner_profile_url'));
            add_filter('peepso_photos_filter_owner_' . self::MODULE_ID,		array(&$this, 'filter_photos_owner'));
            add_filter('peepso_photos_filter_owner_name',       array(&$this, 'filter_photos_owner_name'));

            add_filter('peepso_photos_stream_photos_album',	array(&$this, 'photos_stream_photos_album'));
            add_filter('peepso_photos_profile_photos_album',	array(&$this, 'photos_profile_photos_album'), 10, 2);

            add_filter('peepso_photos_album_url', array(&$this, 'filter_photos_album_url'));

            // hooks for create default album
            add_action('peepso_photos_setup_pages_album',	array(&$this, 'action_setup_page_album'));
            add_action('peepso_action_page_create', 	array(&$this, 'action_setup_page_album'), 10, 1);

            // change avatar & cover section
            add_action('peepso_pages_after_change_avatar', array(&$this, 'action_change_avatar'), 10, 4);
            add_action('peepso_pages_after_change_cover', 	array(&$this, 'action_change_cover'), 10, 2);
            add_filter('peepso_photos_stream_action_change_avatar', 		array(&$this, 'stream_action_change_avatar'), 10, 2);
            add_filter('peepso_photos_stream_action_change_cover', 			array(&$this, 'stream_action_change_cover'), 10, 2);

            // photos item template
            add_filter('peepso_photos_ajax_template_item_album', array(&$this, 'ajax_template_item_album'), 10, 1);
            add_filter('peepso_photos_create_album_privacy_hide', array(&$this, 'create_album_privacy_hide'), 10, 1);
            add_filter('peepso_photos_ajax_create_album_privacy', array(&$this, 'ajax_create_album_privacy'), 10, 1);

            // upload
            add_filter('peepso_photos_stream_action_photo_album', array(&$this, 'photos_stream_action_photo_album'), 10, 2);


            // videos
            add_filter('peepso_videos_filter_owner_name',       array(&$this, 'filter_videos_owner_name'));
            add_filter('peepso_videos_filter_owner_' . self::MODULE_ID,		array(&$this, 'filter_videos_owner'));

            // notifications
            add_action('peepso_action_page_rename', 					array(&$this, 'action_page_rename'), 10, 2);
            add_action('peepso_action_page_privacy_change', 			array(&$this, 'action_page_privacy_change'), 10, 2);

            add_action('peepso_action_page_user_join', 				array(&$this, 'action_page_user_join'), 10, 2);
            add_action('peepso_action_page_user_join_request_accept', 	array(&$this, 'action_page_user_join'), 10, 2);
            add_action('peepso_action_page_add', 				        array(&$this, 'action_page_user_join'), 10, 2);

            add_action('peepso_action_page_user_join_request_send', 	array(&$this, 'action_page_user_join_request_send'), 10, 1);
            add_action('peepso_action_page_user_join_request_accept', array(&$this, 'action_page_user_join_request_accept'), 10, 2);
            add_action('peepso_action_page_user_delete', 				array(&$this, 'action_page_user_delete'), 10, 2);
            add_action('peepso_action_page_user_invitation_accept', 	array(&$this, 'action_page_user_invitation_accept'), 10, 1);


            // extra filter to prevent pinned page post from console
            #add_action('peepso_post_can_be_pinned', array(&$this, 'filter_post_can_be_pinned'));

            // modify notification link
            add_action('peepso_profile_notification_link', array(&$this, 'filter_profile_notification_link'), 10, 2);

            // inject page header to single activity view
            add_action('peepso_activity_single_override_header', array(&$this, 'action_activity_single_override_header'));

            // taggable filter
            add_filter('peepso_taggable', array(&$this, 'filter_taggable'), 10, 2);

            // notifications
            add_filter('peepso_notifications_activity_type', array(&$this, 'notifications_activity_type'), 20, 3);

            // Notify page followers about new posts
            add_action('peepso_pages_new_post', function($page_id, $post_id) {
                $PeepSoNotifications = new PeepSoNotifications();
                $PeepSoPage = new PeepSoPage($page_id);
                $post = get_post($post_id);
                $post_as_page = get_post_meta($post_id, 'peepso_post_as_page', true);

                $from_first_name = PeepSoUser::get_instance($post->post_author)->get_firstname();
                $page_name = $PeepSoPage->get('name');

                if (intval($post_as_page) == 1) {
                    $i18n = __('posted new update', 'pageso');
                    $message = 'posted new update';
                    $args = ['pageso'];
                } else {
                    $i18n = __('posted in %s', 'pageso');
                    $message = 'posted in %s';
                    $args = [
                            'pageso',
                            $page_name
                    ];
                }

                // on-site notifications
                $PeepSoPageFollowers = new PeepSoPageFollowers($page_id, FALSE, NULL, 1);
                $followers = $PeepSoPageFollowers->get_followers();

                $block = new PeepSoBlockUsers();

                if ($post->post_status != 'pending') {
                    foreach($followers as $follower_id) {
                        if($follower_id == $post->post_author || $follower_id == $post->post_author || $block->is_user_blocking($follower_id, $post->post_author)) { continue; }

                        $PeepSoNotifications->add_notification_new($post->post_author, $follower_id, $message, $args,'pages_new_post', self::MODULE_ID, $post_id);
                    }
                }


                // email notifications
                $PeepSoPageFollowers = new PeepSoPageFollowers($page_id, FALSE, NULL, NULL, 1);
                $followers = $PeepSoPageFollowers->get_followers();

                $data = array(
                    'permalink' => PeepSo::get_page('activity_status') . $post->post_title,
                    'fromfirstname' => $from_first_name,
                    'pagename' => $page_name,
                );

                if ($post->post_status != 'pending') {

                    $from_first_name = PeepSoUser::get_instance($post->post_author)->get_firstname();
                    $page_name = $PeepSoPage->get('name');

                    if (intval($post_as_page) == 1) {
                        $i18n = __('%s posted new update', 'pageso');
                        $message = '%s posted new update';
                        $args = [
                            'pageso',
                            $page_name
                        ];
                    } else {
                        $i18n = __('%s posted in %s', 'pageso');
                        $message = '%s posted in %s';
                        $args = [
                            'pageso',
                            $from_first_name,
                            $page_name
                        ];
                    }

                    foreach($followers as $follower_id) {
                        if($follower_id == $post->post_author || $block->is_user_blocking($follower_id, $post->post_author)) { continue; }

                        //check if the user has the right role
                        $userF = PeepSoUser::get_instance($follower_id);
                        $role = $userF->get_user_role();
                        $sendNotificationEmail = in_array($role,array('member','moderator','admin'));
                        $sendNotificationEmail = apply_filters('peepso_pages_follower_send_notification_email', $sendNotificationEmail, $userF);
                        if ($sendNotificationEmail) {
                            PeepSoMailQueue::add_notification_new( $follower_id, $data, $message, $args, 'page_new_post', 'page_new_post', self::MODULE_ID );
                        }
                    }
                }

            },10,2);

            // Notify Admins about new page
            add_action('peepso_action_page_create', function($page_id) {
                if(!PeepSo::get_option('pages_create_notify_admin',0)) {
                    return;
                }

                // send Administrators an email
                $args = array(
                    'role' => 'administrator',
                );

                $user_query = new WP_User_Query($args);
                $users = $user_query->get_results();

                $adm_email = PeepSo::get_notification_emails();

                $is_admin_email = FALSE;
                if (count($users) > 0) {
                    $PeepSoPage = new PeepSoPage($page_id);

                    $data = array(
                        'fromfirstname' => PeepSoUser::get_instance()->get_fullname(),
                        'pagename'     => $PeepSoPage->get('name'),
                        'permalink'     => $PeepSoPage->get('url'),
                    );

                    foreach ($users as $user) {
                        $email = $user->data->user_email;

                        PeepSoMailQueue::add_message($user->ID, $data, __('{sitename} - New Page Created', 'pageso'), 'page_created', 'page_created');
                    }
                }

            });

            // opengraph
            add_filter('peepso_filter_check_opengraph', array(&$this, 'filter_check_opengraph'));


            // Hook into PeepSo routing, enables single item view (eg /pages/?2137/)
            add_filter('peepso_check_query', array(&$this, 'filter_check_query'), 10, 3);
            add_filter('peepso_profile_alerts', function($alerts) {
                if(get_current_user_id()) {
                    global $wpdb;

                    $pages = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}".PeepSoPageFollowers::TABLE." WHERE `pf_user_id`=".get_current_user_id());

                    $result = array();
                    if(count($pages)) {
                        foreach($pages as $page) {
                            $PeepSoPage = new PeepSoPage($page->pf_page_id);

                            if(!$PeepSoPage->id) { continue; }
                            $result[$PeepSoPage->get('name')] = array('id'=>$page->pf_page_id, 'url'=>$PeepSoPage->get_url(), 'email'=>$page->pf_email, 'onsite'=>$page->pf_notify);
                        }
                    }

                    if(count($result)) {
                        uksort($result, "strnatcasecmp");

                        $items = array();
                        foreach($result as $pagename => $pref) {
                            $items[] = array(
                                'label' => $pagename,
                                'setting' => 'page_' . $pref['id'],
                                'loading' => true,
                            );
                        }

                        $alerts['pages'] = array(
                            'title' => __('Page subscriptions','pageso'),
                            'items' => $items
                        );
                    }
                }

                return $alerts;
            }, 20);

            add_filter('peepso_get_notification_value', function($value, $field) {
                if(get_current_user_id() && strstr($field, 'page_')) {
                    $field = explode('_', $field);
                    $page_id = $field[1];
                    $key = $field[2];

                    $PeepSoPageFollower = new PeepSoPageFollower($page_id);

                    if('notification' == $key) {
                        $value = (int) $PeepSoPageFollower->get('notify');
                    } elseif ('email' == $key) {
                        $value = (int) $PeepSoPageFollower->get('email');
                    }
                }

                return $value;
            }, 10, 2);

            add_filter('peepso_save_notifications', function($field) {

                if(!is_array($field) && strstr( $field, 'page_')) {
                    $field=explode('_', $field);

                    $page_id = $field[1];
                    $key = $field[2];

                    $PeepSoInput = new PeepSoInput();
                    $value =$PeepSoInput->int('value');
                    $PeepSoPageFollower = new PeepSoPageFollower($page_id);

                    if('notification' == $key) {
                        $PeepSoPageFollower->set('notify', $value);
                    }

                    if('email' == $key) {
                        $PeepSoPageFollower->set('email', $value);
                    }

                    return array('success'=>1);
                }

                return $field;
            } );

            wp_enqueue_script('peepso-pages-activitystream',
                PeepSo::get_asset('js/activitystream.min.js', __FILE__),
                array('peepso'), self::PLUGIN_VERSION, TRUE);

            if (PeepSo::get_option('disable_questionmark_urls', 0) === 1 && !wp_doing_ajax() && strpos($_SERVER['REQUEST_URI'], 'ajax' ) === FALSE && strpos($_SERVER['REQUEST_URI'], PeepSo::get_option('page_pages') . '/') !== FALSE) {
                add_filter('request', function($q) {
                    if (isset($q['attachment'])) {
                        $q['pagename'] = $q['page'] = $q['attachment'];
                        unset($q['attachment']);
                    }

                    return $q;
                });
            }

            add_filter('peepso_can_disable_comments', function($allow, $post_id) {
                $page_id = get_post_meta($post_id, 'peepso_page_id', true);
                if ($page_id) {
                    $page_user = new PeepSoPageUser($page_id, get_current_user_id());
                    $allow = $page_user->can('manage_content');
                }
                return $allow;
            }, 99, 2);

            add_action('peepso_post_after_cta', function($post_id, $act_id, $act_module_id) {
                if (get_current_user_id()) {
                    $page_id = get_post_meta($post_id, 'peepso_page_id', true);
                    if ($page_id) {
                        $page = new PeepSoPage($page_id);
                        $page_user = new PeepSoPageUser($page_id, get_current_user_id());
                        if (!$page->is_allowed_non_member_actions && !$page_user->can_post_interact()) {
                            $onclick = "ps_page.join($page_id, $post_id, this); return false";
                            echo '<div class="ps-post__call-to-action"><i class="gcis gci-lock"></i><span>';
                            echo sprintf( __('%sLike the page%s to react to or comment on this post.', 'pageso'), '<a href="'.$page->get_url().'" onclick="'.$onclick.'">', '</a>');
                            echo '</span></div>';
                        }
                    }
                }
            }, 10, 3);
        }

        // Emails
        add_filter('peepso_config_email_messages', function($emails) {

            $emails['email_page_new_post'] = array(
                'title' => __('New Post In Page', 'pageso'),
                'description' => __('Notify users about new posts in pages.', 'pageso')
            );

            $emails['email_page_created'] = array(
                'title' => __('New Page Created', 'pageso'),
                'description' => __('Notify Administrators when a new new page is created', 'pageso')
            );

            if (class_exists('PeepSoSharePhotos')){
                $emails['email_user_comment_page_avatar'] = array(
                    'title' => __('User Comment Page Avatar', 'pageso'),
                    'description' => __('This will be sent to a page owner when another user comments on the avatar', 'pageso')
                );

                $emails['email_user_comment_page_cover'] = array(
                    'title' => __('User Comment Page Cover', 'pageso'),
                    'description' => __('This will be sent to a page owner when another user comments on the cover', 'pageso')
                );
            }

            return ($emails);
        });

        add_filter('peepso_config_email_messages_defaults',  function( $emails ) {
            require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . '/install' . DIRECTORY_SEPARATOR . 'activate.php');
            $install = new PeepSoPagesInstall();
            $defaults = $install->get_email_contents();
            return array_merge($emails, $defaults);
        });

        // PeepSo navigation
        add_filter('peepso_navigation', 				array(&$this, 'filter_peepso_navigation'));
        add_filter('peepso_navigation_profile', array(&$this, 'filter_peepso_navigation_profile'));

        if(class_exists('PeepSoSharePhotos')){
            $this->photo_page_system_album = array(
                array(
                    'albumname' => __('Page Avatars', 'pageso'),
                    'albumname_acc' => PeepSo::ACCESS_PUBLIC,
                    'is_system'=> self::MODULE_ID . PeepSoSharePhotos::ALBUM_AVATARS),
                array(
                    'albumname' => __('Page Covers', 'pageso'),
                    'albumname_acc' => PeepSo::ACCESS_PUBLIC,
                    'is_system'=> self::MODULE_ID . PeepSoSharePhotos::ALBUM_COVERS),
                array(
                    'albumname' => __('Page Stream Photos', 'pageso'),
                    'albumname_acc' => PeepSo::ACCESS_PUBLIC,
                    'is_system'=> self::MODULE_ID . PeepSoSharePhotos::ALBUM_STREAM));
        }

        // Compare last version stored in transient with current version
        if( $this::PLUGIN_VERSION.$this::PLUGIN_RELEASE != PeepSo3_MayFly::get($mayfly = 'peepso_'.$this::PLUGIN_SLUG.'_version')) {
            PeepSo3_Mayfly::set($mayfly, $this::PLUGIN_VERSION.$this::PLUGIN_RELEASE);
            $this->activate();
        }
        if(class_exists('PeepSoMaintenanceFactory') && class_exists('PeepSoMaintenancePages')) {
            new PeepSoMaintenancePages();
        }

        $this->input = new PeepSoInput();
    }

    function check_permissions_edit_content($allow, $owner, $author, $allow_logged_out) {
        global $post;

        // avoid overriding the global post var
        $peepso_post = $post;

        // if the object is a comment, find the root post
        $i = 0;
        $act = new PeepSoActivity();
        while('peepso-comment' == $peepso_post->post_type) {
            if (!empty($peepso_post->act_comment_object_id)) {
                $peepso_post = get_post($peepso_post->act_comment_object_id);
            } else {
                $activity_data = $act->get_activity_data($peepso_post->ID);
                $peepso_post = $act->get_activity_post($activity_data->act_id);
            }
            if($i++ > 10) { return FALSE; } // infinite loop precaution
        }

        $page_id = get_post_meta($peepso_post->ID, 'peepso_page_id', true);
        if(!empty($page_id)) {
            $PeepSoPageUser = new PeepSoPageUser($page_id);
            $allow = $PeepSoPageUser->can('edit_content');
        }

        return $allow;
    }
    function check_permissions_delete_content($allow, $owner, $author, $allow_logged_out) {
        global $post;

        // avoid overriding the global post var
        $peepso_post = $post;

        // if the object is a comment, find the root post
        $i = 0;
        $act = new PeepSoActivity();
        while('peepso-comment' == $peepso_post->post_type) {
            if (!empty($peepso_post->act_comment_object_id)) {
                $peepso_post = get_post($peepso_post->act_comment_object_id);
            } else {
                $activity_data = $act->get_activity_data($peepso_post->ID);
                $peepso_post = $act->get_activity_post($activity_data->act_id);
            }
            if($i++ > 10) { return FALSE; } // infinite loop precaution
        }

        $page_id = get_post_meta($peepso_post->ID, 'peepso_page_id', true);
        if(!empty($page_id)) {
            $PeepSoPageUser = new PeepSoPageUser($page_id);
            $allow = $PeepSoPageUser->can('manage_content');
        }

        return $allow;
    }

    /**
     * This function removes privacy dropdown on the post box
     * @param array $interactions is the formated html code that get inserted in the postbox
     * @param array $params
     */
    public function postbox_interactions($interactions, $params = array())
    {
        $is_page_view = FALSE;
        $category_id = FALSE;

        // Don't append "Post to" pages interactions if it's on group context.
        if (PeepSoUrlSegments::get_instance()->_shortcode == 'peepso_groups' && class_exists('PeepSoGroupsPlugin')) {
            return $interactions;
        }

        // Page page or page category
        if (PeepSoUrlSegments::get_instance()->_shortcode == 'peepso_pages') {
            $PeepSoPagesShortcode = PeepSoPagesShortcode::get_instance();

            $is_page_view = $PeepSoPagesShortcode->page_id ? TRUE : FALSE;
            $category_id = $PeepSoPagesShortcode->page_category_id ? $PeepSoPagesShortcode->page_category_id : FALSE;
        }
        // Do not show privacy and schedule postbox dropdown on the page page.
        if ($category_id || $is_page_view) {
            if (class_exists('PeepSoGroupsPlugin')) {
                unset($interactions['groups']);
            }
            unset($interactions['privacy']);
        }

        // Show "post straight to group" postbox dropdown on the frontpage and own's profile page.
        if ( !$is_page_view && ( ! isset($params['is_current_user']) ) || ( isset($params['is_current_user']) && $params['is_current_user'] === TRUE )) {
            $interactions['pages'] = array(
                'icon' => 'gcis pso-i-square-star',
                'icon_html' => '',
                'id' => 'page-tab',
                'class' => 'ps-postbox__menu-item ps-postbox__menu-item--page',
                'click' => 'return;',
                'label' => '',
                'title' => __('Post to', 'pageso'),
                'extra' => PeepSoTemplate::exec_template('pages', 'postbox-interaction', array('category_id'=>$category_id), true),
            );
        }

        return ($interactions);
    }

    public function filter_postbox_options($options, $params = array())
    {
        $is_page_view = FALSE;
        $category_id = FALSE;

        // Page page or page category
        if (PeepSoUrlSegments::get_instance()->_shortcode == 'peepso_pages') {
            $PeepSoPagesShortcode = PeepSoPagesShortcode::get_instance();
            $is_page_view = $PeepSoPagesShortcode->page_id ? TRUE : FALSE;
            $category_id = $PeepSoPagesShortcode->page_category_id ? $PeepSoPagesShortcode->page_category_id : FALSE;
        }


        // Do not show privacy and schedule postbox dropdown on the page page.
        if ($category_id || $is_page_view) {
            if (class_exists('PeepSoGroupsPlugin')) {
                unset($options['groups']);
            }
            unset($options['privacy']);
        }

        // Do not show post_to dropdown on the page page.
        if ($category_id) {
            $options['post_to']['icon'] = 'pso-i-square-star';
            $options['post_to']['label'] = __('Select a page', 'pageso');
        } else if ($is_page_view) {
            unset($options['post_to']);
        }

        return $options;
    }

    public function filter_postbox_options_post_to_options($opts = [])
    {
        $is_page_view = FALSE;
        $is_group_view = FALSE;
        $category_id = FALSE;
        $group_category_id = FALSE;

        // Page category page.
        if (PeepSoUrlSegments::get_instance()->_shortcode == 'peepso_pages') {
            $PeepSoPagesShortcode = PeepSoPagesShortcode::get_instance();
            $is_page_view = $PeepSoPagesShortcode->page_id ? TRUE : FALSE;
            $category_id = $PeepSoPagesShortcode->page_category_id ? $PeepSoPagesShortcode->page_category_id : FALSE;
        }

        if (class_exists('PeepSoGroupsPlugin')) {
            // Group category page.
            if (PeepSoUrlSegments::get_instance()->_shortcode == 'peepso_groups') {
                $PeepSoGroupsShortcode = PeepSoGroupsShortcode::get_instance();
                $is_group_view = $PeepSoGroupsShortcode->group_id ? TRUE : FALSE;
                $group_category_id = $PeepSoGroupsShortcode->group_category_id ? $PeepSoGroupsShortcode->group_category_id : FALSE;
            }
        }

        if ($category_id) {
            $opts = [];
            $opts['pages'] = PeepSoTemplate::exec_template('pages', 'postbox-post-to', ['category_id' => $category_id], TRUE);
        } elseif ($is_page_view || $is_group_view || $group_category_id) {
            unset($opts['pages']);
        } else {
            $opts['pages'] = PeepSoTemplate::exec_template('pages', 'postbox-post-to', NULL, TRUE);
        }

        return $opts;
    }

    /**
     * This function commentsbox if pages is unpublished
     * @param array $interactions is the formated html code that get inserted in the postbox
     * @param array $params
     */
    public function commentsbox_display($display, $post_id = NULL)
    {
        $page_id = get_post_meta($post_id, 'peepso_page_id', true);
        if(!empty($page_id)) {
            // disable commentsbox
            $PeepSoPageUser = new PeepSoPageUser($page_id);
            if(!$PeepSoPageUser->can('post_interact') && !$PeepSoPageUser->can('post_comments_non_members')) {
                $display = FALSE;
            }
        }

        return ($display);
    }

    /*
     * Widgets
     */

    public function register_widgets($widgets)
    {
        // register widgets
        // @TODO that's too hacky - why doesn't autoload work?
        foreach (scandir($widget_dir = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'widgets' . DIRECTORY_SEPARATOR) as $widget) {
            if (strlen($widget)>=5) require_once($widget_dir . $widget);
        }
        return array_merge($widgets, $this->widgets);
    }

    /*
     * PeepSo navigation
     */

    public function filter_peepso_navigation($navigation)
    {
        $user = PeepSoUser::get_instance(get_current_user_id());

        $navigation['pages'] = array(
            'href' => PeepSo::get_page('pages'),
            'label' => _x('Pages', 'Community link', 'pageso'),
            'icon'  => 'gcis pso-i-square-star',

            'primary'           => TRUE,
            'secondary'         => FALSE,
            'mobile-primary'    => TRUE,
            'mobile-secondary'  => FALSE,
            'widget'            => TRUE,
        );

        return ($navigation);
    }

    /*
     * PeepSo profiles
     */

    /**
     * Profile Segments - add link
     * @param $links
     * @return mixed
     */
    public function filter_peepso_navigation_profile($links)
    {
        $links['pages'] = array(
            'label'=> _x('Pages', 'Profile link', 'pageso'),
            'href' => 'pages',
            'icon' => 'gcis pso-i-square-star'
        );

        return $links;
    }

    /*
     * Add links to the profile widget community section
     */
    public function filter_widget_me_community_links($links)
    {
        $links[3][] = array(
            'href' => PeepSo::get_page('pages'),
            'title' => __('Pages', 'pageso'),
            'icon' => 'gcis pso-i-square-star',
        );

        ksort($links);
        return $links;
    }

    /**
     * Profile Segment - adjust the title
     * @param $title
     * @return mixed
     */
    public function filter_page_title_profile_segment( $title )
    {
        if( 'pages' === $title['profile_segment']) {
            $title['newtitle'] = $title['title'] . " - ". __('pages', 'pageso');
        }

        return $title;
    }

    public function peepso_rewrite_profile_pages($pages)
    {
        return array_merge($pages, array('pages'));
    }

    /**
     * Render pages in user profile
     */
    public function filter_profile_segment_pages()
    {
        $pro = PeepSoProfileShortcode::get_instance();
        $this->view_user_id = PeepSoUrlSegments::get_view_id($pro->get_view_user_id());

        wp_enqueue_style('pageso');
        wp_enqueue_script('pageso');

        $this->enqueue_scripts();

        echo PeepSoTemplate::exec_template('pages', 'profile-pages', array('view_user_id' => $this->view_user_id), TRUE);
    }

    /* * * * PeepSo Activity Stream * * * */

    /**
     * todo:docblock
     */
    public function filter_photos_photo_click($click, $photo, $params = array())
    {
        $page_id = get_post_meta($photo->pho_post_id, 'peepso_page_id', true);

        if(intval($photo->pho_module_id) == self::MODULE_ID && !empty($page_id)) {
            $params = empty($params) ? array() : $params;
            $params_page = array_merge($params, array('module_id' => self::MODULE_ID, 'page_id' => $page_id, 'post_as_page' => 1 ));
            $click = "return ps_comments.open('" . $photo->pho_id . "', 'photo', null, " . str_replace('"', "'", json_encode( $params_page )) . ');';
        }

        return $click;
    }

    /**
     * todo:docblock
     */
    public function filter_photos_photo_item_click($click, $photo, $params = array())
    {
        $page_id = get_post_meta($photo->pho_post_id, 'peepso_page_id', true);

        if(intval($photo->pho_module_id) == self::MODULE_ID && !empty($page_id)) {
            $params = empty($params) ? array() : $params;
            $params_page = array_merge($params, array( 'module_id' => self::MODULE_ID, 'page_id' => $page_id, 'post_as_page' => 1 ));
            $click = "return ps_comments.open('" . $photo->pho_id . "', 'photo', null, " . str_replace('"', "'", json_encode( $params_page )) . '); return false;';
        }

        return $click;
    }

    /**
     * todo:docblock
     */
    public function filter_photos_photo_set_as_avatar($click, $photo_id, $params = array())
    {
        $photo_model = new PeepSoPhotosModel();
        $photo = $photo_model->get_photo($photo_id);
        if(NULL !== $photo){
            $page_id = get_post_meta($photo->pho_post_id, 'peepso_page_id', true);

            if(intval($photo->pho_module_id) == self::MODULE_ID && !empty($page_id)) {

                $req = '';
                if(count($params) > 0) {
                    $req = array();
                    foreach ($params as $key => $value) {
                        $req[] = $key . ': \'' . $value . '\'';
                    }
                    $req = implode(',', $req) . ',';
                }

                $click = 'peepso.photos.set_as_avatar({' . $req . ' module_id: '. self::MODULE_ID.', page_id: ' . $page_id . '});';
            }
        }

        return $click;
    }

    /**
     * todo:docblock
     */
    public function filter_photos_photo_set_as_cover($click, $photo_id, $params = array())
    {
        $photo_model = new PeepSoPhotosModel();
        $photo = $photo_model->get_photo($photo_id);
        if(NULL !== $photo){
            $page_id = get_post_meta($photo->pho_post_id, 'peepso_page_id', true);

            if(intval($photo->pho_module_id) == self::MODULE_ID && !empty($page_id)) {

                $req = '';
                if(count($params) > 0) {
                    $req = array();
                    foreach ($params as $key => $value) {
                        $req[] = $key . ': \'' . $value . '\'';
                    }
                    $req = implode(',', $req) . ',';
                }

                $click = 'peepso.photos.set_as_cover({' . $req . ' module_id: '. self::MODULE_ID.', page_id: ' . $page_id . '});';
            }
        }

        return $click;
    }

    /**
     * Modify the clauses to filter posts
     * @param  array $clauses
     * @param  int $user_id The owner of the activity stream
     * @return array
     */
    public function filter_photos_post_clauses($clauses, $module_id, $widgets)
    {
        global $wpdb;

        if($module_id == self::MODULE_ID) {
            // Filter for pages joined
            $clauses['join'] .= ' LEFT JOIN `' . $wpdb->prefix . PeepSoPhotosAlbumModel::TABLE . '` `am` ON ' .
                ' `' . $wpdb->prefix . PeepSoPhotosModel::TABLE . '`.`pho_album_id` = `am`.`pho_album_id` ' ;
            /*$clauses['join'] .= ' LEFT JOIN `' . $wpdb->postmeta  . '` `peepso_page_meta` ON ' .
                                    ' `am`.`pho_owner_id` = `peepso_page_meta`.`post_id` AND `peepso_page_meta`.`meta_key` = \'peepso_page_id\' ' ;*/

            $page_id = $this->input->int('page_id', 0);
            if(0 !== $page_id) {
                $clauses['where'] .= " AND (`am`.`pho_owner_id` = '" . $page_id . "') ";
            }
        }

        if($widgets) {
            // Filter for pages joined
            $clauses['join'] .= ' LEFT JOIN `' . $wpdb->postmeta  . '` `pmeta_pages` ON ' .
                ' `' . $wpdb->posts . '`.`ID` = `pmeta_pages`.`post_id` AND `pmeta_pages`.`meta_key` = \'peepso_page_id\' ' ;

            $clauses['where'] .= " AND (`pmeta_pages`.`meta_value` IS NULL) ";
        }

        return $clauses;
    }

    /**
     * Add extra filter to prevent pinned page post from console
     * @param array $post
     * @return array $post
     */
    public function filter_post_can_be_pinned($post) {
        return $post;
        $page_id = get_post_meta($post->ID, 'peepso_page_id', true);

        if (!empty($page_id)) {
            $post->can_be_pinned = 0;
        } else {
            $post->can_be_pinned = 1;
        }
        return $post;
    }

    /**
     * Modify link notification
     * @param array $link
     * @param array $note_data
     * @return string $link
     */
    public function filter_profile_notification_link($link, $note_data)
    {
        $not_types = array(
            'pages_rename',
            'pages_publish',
            'pages_unpublish',

            'pages_user_join',
            'pages_user_join_request_send',
            'pages_user_join_request_accept',

            'pages_user_invitation_send',
            'pages_user_invitation_accept',

            'pages_privacy_change',
        );

        // @todo delete legacy not types in January after the old notifications are out of the system
        $legacy_not_types = array('join_page', 'rename_page', 'page_invited', 'page_accepted', 'publish_page', 'unpublish_page');
        $not_types = array_merge($not_types, $legacy_not_types);

        $not_type = $note_data['not_type'];

        if (in_array($not_type, $not_types)) {
            $page = new PeepSoPage($note_data['not_external_id']);
            $link = $page->get_url(FALSE);

            if('pages_user_join_request_send' == $not_type) {
                $link.='members/pending';
            }
        }
        return $link;
    }

    /**
     * modify onclick handler delete post for album type post
     * @param array $options
     * @return array $options
     */
    public function post_filters($options) {
        $post = $options['post'];
        $options_acts = $options['acts'];

        $page_id = get_post_meta($post->ID, 'peepso_page_id', true);

        if(!empty($page_id)) {
            // if type is photo album, show latest photos as first ones.
            if (isset($options_acts['delete'])) {
                $options_acts['delete']['click'] = 'return activity.action_delete(' . $post->ID . ', {module_id: '. self::MODULE_ID.', page_id: ' . $page_id . '});';
            }

            unset($options_acts['repost']);
        }

        if(self::is_join_notification($post)) {

            // disable "edit"
            if (isset($options_acts['edit'])) {
                unset($options_acts['edit']);
            }
        }

        $options['acts'] = $options_acts;

        return $options;
    }

    /**
     * modify onclick handler delete post for album type post
     * @param array $options
     * @return array $options
     */
    public function post_filters_after($options) {
        return $this->modify_post_actions($options);
    }

    /**
     * Change act_id on repost button act_id to follow parent's act_id.
     * @param array $options The default options per post
     * @return  array
     */
    public function modify_post_actions($options)
    {
        $post = $options['post'];

        $page_id = get_post_meta($post->ID, 'peepso_page_id', true);

        // fix photos post ID in modal comments
        // wrong post ID information
        if(class_exists('PeepSoSharePhotos')){
            $_photos_model = new PeepSoPhotosModel();
            if(intval($post->act_module_id) == PeepSoSharePhotos::MODULE_ID && empty($page_id)) {
                $photo = $_photos_model->get_photo($post->ID);
                if(isset($photo->pho_module_id) && $photo->pho_module_id == self::MODULE_ID) {
                    $page_id = get_post_meta($photo->pho_post_id, 'peepso_page_id', true);
                }
            }
        }

        // if type is photo album, show latest photos as first ones.
        if(!empty($page_id)) {

            if(isset($options['acts']['delete']['click'])) {
                // modify delete script
                $delete_script = preg_replace( '/,\s*\{\s*module_id[^\}]+\}/', '', $options['acts']['delete']['click']);
                $delete_script = str_replace( ');', ', {module_id: '. self::MODULE_ID.', page_id: ' . $page_id . '});', $delete_script);

                $options['acts']['delete']['click'] = $delete_script;
            }

            // disable repost function for page post
            unset($options['acts']['repost']);

            // disable like button when page is unpublished
            $PeepSoPageUser = new PeepSoPageUser($page_id);
            if(!$PeepSoPageUser->can('post_interact') && !$PeepSoPageUser->can('post_likes_non_members')) {
                // disable repost function for page post
                unset($options['acts']['like']);
            }
        }

        return ($options);
    }

    /**
     * Change act_id on repost button act_id to follow parent's act_id.
     * @param array $options The default options per post
     * @return  array
     */
    public function modify_comments_actions($options)
    {
        global $post;

        $parent_post = get_post($post->act_comment_object_id);

        if (!$parent_post) {
            return ($options);
        }

        if($parent_post->post_type === PeepSoActivityStream::CPT_COMMENT) {
            $peepso_activity = new PeepSoActivity();
            $parent_act = $peepso_activity->get_activity_data($post->act_comment_object_id, $post->act_comment_module_id);
            $parent_post = $peepso_activity->get_activity_post($parent_act->act_id);
            $page_id = get_post_meta($parent_post->act_comment_object_id, 'peepso_page_id', true);
        } else {
            $page_id = get_post_meta($post->act_comment_object_id, 'peepso_page_id', true);
        }

        // if type is photo album, show latest photos as first ones.
        if(!empty($page_id)) {
            // disable like button when page is unpublished
            $PeepSoPageUser = new PeepSoPageUser($page_id);
            if(!$PeepSoPageUser->can('post_interact')) {
                if (!$PeepSoPageUser->can('post_likes_non_members')) {
                    unset($options['like']);
                }
                if (!$PeepSoPageUser->can('post_comments_non_members')) {
                    unset($options['reply']);
                }
            }

            if (isset($options['reply'])) {
                if ( 1 == PeepSo::get_option_new('mentions_auto_on_comment_reply') ) {
                    $page = new PeepSoPage($page_id);
                    $page_owner_id = $page->owner_id;
                    if ($page_owner_id == $post->post_author && $page_owner_id != get_current_user_id()) {
                        $author_data = '{ id: \'page' . $page_id . '\', name: \'' . esc_js( trim(strip_tags($page->get('name'))) ) . '\' }';
                        $options['reply']['click'] = 'activity.comment_action_reply(' . $post->act_id . ', ' . $post->ID . ', this, ' . $author_data . '); return false;';
                    }
                }
            }
        }

        return ($options);
    }

    /**
     * Modify the clauses to filter posts
     * @param  array $clauses
     * @param  int $user_id The owner of the activity stream
     * @return array
     */

    public function filter_post_clauses($clauses, $user_id = NULL) {


        if (!is_null($user_id) && (strpos($clauses['where'], PeepSoActivityStream::CPT_COMMENT) === false)) {
            global $wpdb;

            // Filter for pages joined
            $clauses['join'] .= ' LEFT JOIN `' . $wpdb->postmeta  . '` `peepso_page_meta` ON ' .
                ' `' . $wpdb->posts . '`.`ID` = `peepso_page_meta`.`post_id` AND `peepso_page_meta`.`meta_key` = \'peepso_page_id\' ' .
                ' LEFT JOIN `' . $wpdb->postmeta  . '` `peepso_page_priv` ON ' .
                ' `peepso_page_priv`.`post_id` = `peepso_page_meta`.`meta_value` AND `peepso_page_priv`.`meta_key` = \'peepso_page_privacy\' ' .
                ' LEFT JOIN `' . $wpdb->prefix . PeepSoPageUsers::TABLE  . '` `peepso_page_member` ON ' .
                ' `peepso_page_meta`.`meta_value` = `peepso_page_member`.`pm_page_id` AND peepso_page_member.pm_user_id = ' . get_current_user_id(). ' ' .
                ' LEFT JOIN `' . $wpdb->posts . '` `peepso_page` ON ' .
                ' `peepso_page_meta`.`meta_value` = `peepso_page`.`ID` ';

            #$stream_id  = $this->input->val ('stream_id',    'core_community');
            $page_id   = $this->input->int('page_id',     0);
            $page_category_id   = $this->input->int('page_category_id',     53);
            #$module_id  = $this->input->int('module_id',    0);
            #$post_id    = $this->input->int('post_id',      0);
            #$profile_id = $this->input->int('uid',          0);




            // GROUP VIEW
            // SQL safe, not used in query
            if( in_array($this->input->value('context', '', FALSE), array('page') ) ) {
                $clauses['where'] .= $wpdb->prepare(" AND `peepso_page_meta`.`meta_value` IS NOT NULL AND `peepso_page`.`ID` = %d ", $page_id);
            }

            // GROUP CATEGORY VIEW
            // SQL safe, not used in query
            if( in_array($this->input->value('context', '', FALSE), array('page-category') ) ) {
                $page_ids = PeepSoPageCategoriesPages::get_page_ids_for_category($page_category_id);

                $clauses['where'] .= " AND `peepso_page_meta`.`meta_value` IS NOT NULL AND `peepso_page`.`ID` IN (" . implode(',',$page_ids) . ")";
            }

            // HIDE SECRET AND CLOSED FROM NON-MEMBERS NON-ADMIN
            if(!PeepSo::is_admin()) {

                $clauses['where'] .= "
                AND (
                        `peepso_page_meta`.`meta_value` IS NULL
                        OR
                        (
                            peepso_page_priv.meta_value = 0
                            OR
                            (
                                peepso_page_priv.meta_value IN(1,2)
                                AND
                                substr(peepso_page_member.pm_user_status, 1, 6) = 'member'
                            )
                        )
                   )
                ";
            }


        }

        return $clauses;
    }

    public function activity_meta_query_args($args, $module_id)
    {
        return $args;
        if($module_id == PeepSoPagesPlugin::MODULE_ID) {

            if(!isset($args['meta_query'])) {
                $args['meta_query'] = array();
            }

            array_push($args['meta_query'],
                array(
                    'compare' => 'EXISTS',
                    'key' => 'peepso_page_id',
                )
            );
        }

        return $args;
    }

    /**
     * This function add information after new activity on page
     * @param int $post_id The post ID
     * @param int $act_id The activity ID
     */
    public function after_add_post($post_id, $act_id)
    {
        $page_id = $this->input->int('page_id', 0);
        $module_id = $this->input->int('module_id', 0);

        if((0 !== $page_id || 0 !== $module_id)
            && self::MODULE_ID == $module_id) {

            $post = get_post($post_id);
            if (!is_object($post) || !isset($post->post_type) || $post->post_type != 'peepso-post') {
                return;
            }

            $files = $this->input->value('files', array(), FALSE);

            // SQL safe, not used in queries
            if (count($files) > 0 && 'photo' === $this->input->value('type','',FALSE)) {
                // migrate from activate function,
                // setup album before uploading avatar
                $this->action_setup_page_album($page_id);
            }

            update_post_meta($post_id, 'peepso_page_id', $page_id);

            // Handle post as page
            $post_as_page = $this->input->int('post_as_page', 0);
            if ($post_as_page) {
                update_post_meta($post_id, 'peepso_post_as_page', 1);
            }

            if($post->post_status == 'future') {
                do_action('peepso_pages_new_scheduled_post', $page_id, $post_id);
            } else {
                do_action('peepso_pages_new_post', $page_id, $post_id);
            }
        }
    }

    /**
     * This function add information after new comment on page post
     * @param int $post_id The post ID
     * @param int $act_id The activity ID
     */
    public function after_add_comment($post_id, $act_id, $did_notify, $did_email) {
        $peepso_activity = new PeepSoActivity();

        // get root post
        $comment = $peepso_activity->get_comment($post_id);
		$comment = $comment->post;

		if ($comment) {
			$root_act = $peepso_activity->get_activity_data($comment->act_comment_object_id, $comment->act_comment_module_id);
			$root_post = $peepso_activity->get_activity_post($root_act->act_id);

			// if root post still a comment
			if ($root_post->post_type == PeepSoActivityStream::CPT_COMMENT) {
				$comment = $root_post;
				$root_act = $peepso_activity->get_activity_data($comment->act_comment_object_id, $comment->act_comment_module_id);
				$root_post = $peepso_activity->get_activity_post($root_act->act_id);
			}
			if ($root_post->post_type == PeepSoActivityStream::CPT_POST) {
				$page_id = get_post_meta($root_post->ID,'peepso_page_id',TRUE);
                if(strlen($page_id) && is_numeric($page_id)) {
                    update_post_meta($post_id, 'peepso_page_id', $page_id);

                    // Handle post as page
                    $post_as_page = $this->input->int('post_as_page', 0);
                    $page = new PeepSoPage($page_id);
                    $pageUser = new PeepSoPageUser($page_id, get_current_user_id(), $page);
                    if ($post_as_page || $pageUser->can_override_author()) {
                        update_post_meta($post_id, 'peepso_post_as_page', 1);
                    }
                }
			}
		}


    }

    public function future_to_publish(WP_Post $post) {
        $post_id = $post->ID;

        $page_id = get_post_meta($post_id,'peepso_page_id',TRUE);

        if(strlen($page_id) && is_numeric($page_id)) {
            do_action('peepso_pages_new_post', $page_id, $post_id);
        }
    }

    public function filter_activity_commentsbox_author_avatar($output, $post_id, $post_author, $PeepSoUser)
    {
        $page_id = get_post_meta($post_id, 'peepso_page_id', true);
        $post_as_page = get_post_meta($post_id, 'peepso_post_as_page', true);
        if ($page_id > 0 && intval($post_as_page) == 1) {
            $page = new PeepSoPage($page_id);
            $pageUser = new PeepSoPageUser($page_id, get_current_user_id(), $page);
            if ($pageUser->can_override_author()) {
                return '<a class="ps-avatar cstream-avatar cstream-author" href="' . $page->get_url() . '"><img data-author="' . $post_author . '" src="'. $page->get_avatar_url() . '" alt="" /></a>';
            }
        }

        return $output;
    }

    public function filter_mention_info($info, $id)
    {
        if (preg_match('/page(\d+)/', $id, $matches)) {
            $page = new PeepSoPage($matches[1]);
            $info['name'] = trim(strip_tags($page->get('name')));
            $info['url'] = $page->get_url();
        }

        return $info;
    }

    public function filter_notification_avatar($output, $not_id, $PeepSoUser)
    {
        $notification = new PeepSoNotifications(intval($not_id));
        $data = $notification->get_data();
        if (NULL !== $data) {
            $post_id = $data->not_external_id;
            $page_id = get_post_meta($post_id, 'peepso_page_id', true);
            if ($page_id > 0) {
                $page = new PeepSoPage($page_id);
                $page_user = new PeepSoPageUser($page_id, $data->not_from_user_id, $page);
                if ($page_user->can_override_author()) {
                    return '<img src="' . $page->get_avatar_url(). '" alt="' . trim(strip_tags($page->get('name'))). '">';
                }
            }
        }

        return $output;
    }

    public function filter_notification_user_firstname($output, $not_id, $PeepSoUser)
    {
        $notification = new PeepSoNotifications(intval($not_id));
        $data = $notification->get_data();
        if (NULL !== $data) {
            $post_id = $data->not_external_id;
            $page_id = get_post_meta($post_id, 'peepso_page_id', true);
            if ($page_id > 0) {
                $page = new PeepSoPage($page_id);
                $page_user = new PeepSoPageUser($page_id, $data->not_from_user_id, $page);
                if ($page_user->can_override_author()) {
                    return trim(strip_tags($page->get('name')));
                }
            }
        }

        return $output;
    }

    public function filter_postbox_author_avatar($output)
    {
        // Page page or page category
        if (PeepSoUrlSegments::get_instance()->_shortcode == 'peepso_pages') {
            $PeepSoPagesShortcode = PeepSoPagesShortcode::get_instance();
            $is_page_view = $PeepSoPagesShortcode->page_id ? TRUE : FALSE;
            if ($is_page_view) {
                $page = new PeepSoPage($PeepSoPagesShortcode->page_id);
                return '<a href="'.$page->get_url().'"><img class="pso-avatar__image" src="'.$page->get_avatar_url() .'" alt="'.$page->get('name') .'"></a>';
            }
        }

        return $output;
    }

    public function filter_postbox_author_fullname($output)
    {
        // Page page or page category
        if (PeepSoUrlSegments::get_instance()->_shortcode == 'peepso_pages') {
            $PeepSoPagesShortcode = PeepSoPagesShortcode::get_instance();
            $is_page_view = $PeepSoPagesShortcode->page_id ? TRUE : FALSE;
            if ($is_page_view) {
                $page = new PeepSoPage($PeepSoPagesShortcode->page_id);
                return '<span><span>' . $page->get('name') . '</span> <span data-ps="title-extra" style="font-weight:normal"></span></span>';
            }
        }

        return $output;
    }

    /**
     * PeepSo stream action title
     * @param $title default stream action title
     * @param $post global post variable
     */
    public function filter_activity_stream_author($output, $post_id, $hide_post_header, $post_author, $PeepSoUser)
    {
        $page_id = get_post_meta($post_id, 'peepso_page_id', true);
        $post_as_page = get_post_meta($post_id, 'peepso_post_as_page', true);
        $is_join_notification = FALSE;
        if(strlen(get_post_meta($post_id, self::JOIN_STREAM_META, TRUE))) {
            $is_join_notification = TRUE;
        }
        if ($page_id > 0 && intval($post_as_page) == 1 && !$is_join_notification) {
            $page = new PeepSoPage($page_id);
            return '<a ' . $hide_post_header . ' class="ps-avatar ps-avatar--post" href="' . $page->get_url() . '"><img data-author="' . $post_author . '" src="'. $page->get_avatar_url().'" alt="' .$page->get('name').' avatar" /></a>';
        }

        return $output;
    }

    /**
     * PeepSo stream action title
     * @param $title default stream action title
     * @param $post global post variable
     */
    public function filter_activity_stream_comments_author_avatar($output, $post_id, $post_author, $PeepSoUser)
    {
        $page_id = get_post_meta($post_id, 'peepso_page_id', true);
        $post_as_page = get_post_meta($post_id, 'peepso_post_as_page', true);
        if ($page_id > 0 && intval($post_as_page) == 1) {
            $page = new PeepSoPage($page_id);
            return '<a href="' . $page->get_url() . '"><img data-author="' . $post_author . '" src="'. $page->get_avatar_url().'" alt="' .$page->get('name').' avatar" /></a>';
        }

        return $output;
    }

    public function filter_activity_stream_comments_author_name($output, $post_id, $post_author, $fullName)
    {
        $page_id = get_post_meta($post_id, 'peepso_page_id', true);
        $post_as_page = get_post_meta($post_id, 'peepso_post_as_page', true);
        if ($page_id > 0 && intval($post_as_page) == 1) {
            $page = new PeepSoPage($page_id);
            return '<strong>' . $page->get('name') . '</strong>';
        }

        return $output;
    }

    /**
     * PeepSo stream action title
     * @param $title default stream action title
     * @param $post global post variable
     */
    public function filter_activity_stream_title($title, $post, $action)
    {
        $PeepSoActivityShortcode = PeepSoActivityShortcode::get_instance();
        if( $PeepSoActivityShortcode->is_permalink_page()) {
            return $title;
        }

        $page_id = '';
        $post_as_page = FALSE;
        if(is_null($post->act_description)) {
            $page_id = get_post_meta($post->ID, 'peepso_page_id', true);
            $post_as_page = get_post_meta($post->ID, 'peepso_post_as_page', true);
        }

        // fix photos post ID in modal comments
        // wrong post ID information
        // if(class_exists('PeepSoSharePhotos')){
        // 	$_photos_model = new PeepSoPhotosModel();
        // 	if(intval($post->act_module_id) == PeepSoSharePhotos::MODULE_ID && empty($page_id)) {
        // 		$photo = $_photos_model->get_photo($post->ID);
        // 		if(isset($photo->pho_module_id) && $photo->pho_module_id == self::MODULE_ID) {
        // 			$page_id = get_post_meta($photo->pho_post_id, 'peepso_page_id', true);
        // 		}
        // 	}
        // }

        $module_id = $this->input->int('module_id', 0);
        $page_id = $this->input->int('page_id', $page_id);
        $post_as_page = $this->input->int('post_as_page', $page_id);
        $force_as_page_post = $this->input->int('force_as_page_post', 0);

        $is_join_notification = self::is_join_notification($post);

        // page post outside of page stream
        $update_post_title = !empty($page_id) && (trim(strval(PeepSoUrlSegments::get_instance()->_shortcode)) != 'peepso_pages') && ($module_id !== self::MODULE_ID || $force_as_page_post);
        $update_post_title = apply_filters('peepso_pages_update_post_title', $update_post_title);

        $page = new PeepSoPage($page_id);
        if ($update_post_title) {
            $author = PeepSoUser::get_instance($post->post_author);

            if(FALSE !== $page) {
                ob_start();
                do_action('peepso_action_render_user_name_before', $author->get_id());
                $before_fullname = ob_get_clean();

                ob_start();
                do_action('peepso_action_render_user_name_after', $author->get_id());
                $after_fullname = ob_get_clean();

                $PeepSoPageUser = new PeepSoPageUser($page_id);
                $meta = "";
                if(get_current_user_id() && 1 == PeepSo::get_option('pages_meta_in_stream', 0)) {
                    if ($PeepSoPageUser->is_member) {
                        $meta .= sprintf(__('You are the page %s', 'pageso'), $PeepSoPageUser->role_l8n);
                    } else {
                        $meta .= __('You are not a follower of this page', 'pageso');
                    }

                    $PeepSoPageFollower = new PeepSoPageFollower($page_id);

                    if ($PeepSoPageFollower->follow) {
                        $meta .= "\n" . __('Follow', 'pageso');
                    }

                    if ($PeepSoPageFollower->notify) {
                        $meta .= "\n" . __('Be notified', 'pageso');
                    }

                    if ($PeepSoPageFollower->email) {
                        $meta .= "\n" . __('Receive emails', 'pageso');
                    }
                }

                if($is_join_notification) {
                    $action_title = PeepSo::get_option('pages_join_post_action_text_other', __('likes','pageso'));
                    if (empty($action_title)) {
                        $action_title = __('likes','pageso');
                    }
                    $title = sprintf(
                        '<a class="ps-post__author" href="%s" data-hover-card="%d">%s</a> '. $action_title . ' <a class="ps-post__subtitle" href="%s" title="%s"><i class="gcis pso-i-square-star"></i>%s</a><span class="ps-post__title-desc ps-stream-action-title">%s</span> ',
                        $author->get_profileurl(), $author->get_id(), $before_fullname . $author->get_fullname() . $after_fullname,
                        $page->get_url(), $meta, $page->get('name'), $action
                    );
                } else {
                    $title = sprintf(
                        '<a class="ps-post__author" href="%s" data-hover-card="%d">%s</a><i class="gcis gci-angle-right"></i>'
                        .'<a class="ps-avatar" href="%s" title="%s"><img src="%s"/></a>'
                        .'<i class="gcis pso-i-square-star ps-post-author-page-indicator"></i><a class="ps-post__subtitle" href="%s" title="%s">%s</a><span class="ps-post__title-desc ps-stream-action-title">%s</span> ',
                        $author->get_profileurl(), $author->get_id(), $before_fullname . $author->get_fullname() . $after_fullname,
                        $page->get_url(), $meta, $page->get_avatar_url_full(),
                        $page->get_url(), $meta, $page->get('name'), $action

                    );
                }
            }
        }

        if ($post_as_page && !$is_join_notification) {
            $title = sprintf(
                '<a class="ps-post__author" href="%s" data-ps="page-post-indicator">%s</a><span class="ps-post__title-desc ps-stream-action-title">%s</span> ',
                $page->get_url(), $page->get('name'), trim($action)
            );
        }

        // page post inside stream
        if(!empty($page_id) && (trim(strval(PeepSoUrlSegments::get_instance()->_shortcode)) == 'peepso_pages') || ($module_id === self::MODULE_ID)) {
            if($is_join_notification) {
                $action_title = PeepSo::get_option('pages_join_post_action_text_page', __('liked this page','pageso'));
                if (empty($action_title)) {
                    $action_title = __('liked this page','pageso');
                }
                $title .= ' '. $action_title;
            }
        }

        return ($title);
    }

    public static function is_join_notification($post) {
        $is_join_notification = FALSE;

        if(strlen(get_post_meta($post->ID, self::JOIN_STREAM_META, TRUE))) {
            $is_join_notification = TRUE;
        }

        return $is_join_notification;
    }
    /**
     * Remove peepso_pages shortcode
     * @param string $string to process
     * @return string $string
     */
    public function filter_activity_remove_shortcode( $content )
    {
        foreach($this->shortcodes as $shortcode=>$class) {
            $from = array('['.$shortcode.']','['.$shortcode);
            $to = array('&#91;'.$shortcode.'&#93;', '&#91;'.$shortcode);
            $content = str_ireplace($from, $to, $content);
        }
        return $content;
    }

    public function filter_activity_has_privacy($has_privacy)
    {
        global $post;
        $page_id = get_post_meta($post->ID, 'peepso_page_id', true);
        if(!empty($page_id)) {
            return FALSE;
        }

        return $has_privacy;
    }

    /* * * * Photos * * * * */
    public function photos_pages_dir($photo_dir)
    {

        // check post parameters if 'page_id' and module_id is exist
        $page_id = $this->input->int('page_id', 0);
        $module_id = $this->input->int('module_id', 0);

        if($photo_dir === NULL && $module_id === self::MODULE_ID && 0 !== $page_id) {
            $page = new PeepSoPage($page_id);
            if(FALSE !== $page) {
                $photo_dir = ($page) ? $page->get_image_dir() : '';
                $photo_dir .= 'photos' . DIRECTORY_SEPARATOR;
            }
        }

        return ($photo_dir);
    }

    public function photos_pages_url($photo_dir = '')
    {
        // check post parameters if 'page_id' and module_id is exist
        $page_id = $this->input->int('page_id', 0);
        $module_id = $this->input->int('module_id', 0);

        if(empty($photo_dir) && $module_id === self::MODULE_ID && 0 !== $page_id) {
            $page = new PeepSoPage($page_id);
            if(FALSE !== $page) {
                $photo_dir = $page->get_image_url();
            }
        }

        return ($photo_dir);
    }

    public function photos_pages_thumbs_url($photo_url, $thumbs)
    {
        // check post parameters if 'page_id' and module_id is exist
        $page_id = $this->input->int('page_id', 0);
        $module_id = $this->input->int('module_id', 0);

        if($photo_url === NULL && $module_id === self::MODULE_ID && 0 !== $page_id) {
            $page = new PeepSoPage($page_id);
            if(FALSE !== $page) {
                $photo_url = $page->get_image_url() . 'photos/thumbs/' . $thumbs;
            }
        }

        return ($photo_url);
    }

    public function post_photos_pages_location($photo_url, $post_id, $type)
    {
        $page_id = get_post_meta($post_id, 'peepso_page_id', true);

        if(!empty($page_id)) {
            $page = new PeepSoPage($page_id);
            if(FALSE !== $page) {
                $photo_url = $page->get_image_url() . 'photos/';
                if($type == 'thumbs') {
                    $photo_url = $page->get_image_url() . 'photos/thumbs/';
                }
            }
        }

        return ($photo_url);
    }

    public function access_content($allow, $shortcode, $module)
    {
        global $wpdb;

        $PeepSoActivityShortcode = PeepSoActivityShortcode::get_instance();

        if($PeepSoActivityShortcode->is_permalink_page()) {
            $sql = 'SELECT `ID`, `act_access`, `act_owner_id` ' .
                " FROM `{$wpdb->posts}` " .
                " LEFT JOIN `{$wpdb->prefix}" . PeepSoActivity::TABLE_NAME . "` ON `act_external_id`=`{$wpdb->posts}`.`ID` " .
                ' WHERE `post_name`=%s AND `post_type`=%s ' .
                ' LIMIT 1 ';
            $ret = $wpdb->get_row($wpdb->prepare($sql, $PeepSoActivityShortcode->get_permalink(), PeepSoActivityStream::CPT_POST));

            if($ret !== NULL)
            {
                $page_id = get_post_meta($ret->ID, 'peepso_page_id', true);
                if(!empty($page_id))
                {
                    $PeepSoPageUser= new PeepSoPageUser($page_id);
                    $allow = $PeepSoPageUser->can('access');
                }
            }
        }

        return $allow;
    }

    public function access_message($message)
    {
        global $wpdb;

        $PeepSoActivityShortcode = PeepSoActivityShortcode::get_instance();

        if($PeepSoActivityShortcode->is_permalink_page()) {
            $sql = 'SELECT `ID`, `act_access`, `act_owner_id` ' .
                " FROM `{$wpdb->posts}` " .
                " LEFT JOIN `{$wpdb->prefix}" . PeepSoActivity::TABLE_NAME . "` ON `act_external_id`=`{$wpdb->posts}`.`ID` " .
                ' WHERE `post_name`=%s AND `post_type`=%s ' .
                ' LIMIT 1 ';
            $ret = $wpdb->get_row($wpdb->prepare($sql, $PeepSoActivityShortcode->get_permalink(), PeepSoActivityStream::CPT_POST));

            if($ret !== NULL)
            {
                $page_id = get_post_meta($ret->ID, 'peepso_page_id', true);
                if(!empty($page_id))
                {
                    $PeepSoPageUser = new PeepSoPageUser($page_id);

                    if(!$PeepSoPageUser->can('access'))
                    {
                        $message = PeepSoTemplate::do_404();
                    }
                }
            }
        }

        return $message;
    }

    /* * * * * * PHOTO ALBUM  * * * * * * */

    /**
     * todo:docblock
     */
    public function filter_photos_owner_album($owner)
    {
        $module_id = $this->input->int('module_id', 0);
        $page_id = $this->input->int('page_id', 0);

        if(self::MODULE_ID === $module_id && 0 !== $page_id) {
            $owner = $page_id;
        }

        $post_id = $this->input->int('post_id', 0);
        if (!$module_id && $post_id) {
            $page_id = get_post_meta($post_id, 'peepso_page_id', true);
            if ($page_id) {
                $owner = $page_id;
            }
        }

        return($owner);
    }

    /**
     * filter_photos_album_url
     */
    public function filter_photos_album_url($album_url)
    {
        $url_segments = PeepSoUrlSegments::get_instance();
        if ($url_segments->_shortcode == 'peepso_pages') {
            $page_id = $url_segments->get(1);

            $page = new PeepSoPage($page_id);
            $album_url = $page->get_url() . 'photos/album';
        }

        return($album_url);
    }

    /**
     * ajax_template_item_album
     */
    public function ajax_template_item_album($template)
    {
        $module_id = $this->input->int('module_id', 0);
        $page_id = $this->input->int('page_id', 0);

        if(self::MODULE_ID === $module_id && 0 !== $page_id) {
            $template = 'photo-page-item-album';
        }

        return $template;
    }

    /**
     * create_album_privacy_hide
     */
    public function create_album_privacy_hide($hide)
    {
        if (PeepSoUrlSegments::get_instance()->_shortcode == 'peepso_pages') {
            $hide = true;
        }

        return $hide;
    }

    public function ajax_create_album_privacy($privacy)
    {
        $module_id = $this->input->int('module_id', 0);
        $page_id = $this->input->int('page_id', 0);

        if(self::MODULE_ID === $module_id && 0 !== $page_id) {
            // todo : @page privacy
            $privacy = PeepSo::ACCESS_PUBLIC;
        }

        return $privacy;
    }

    /**
     * todo:docblock
     */
    public function filter_photos_owner_profile_url($profile_url)
    {
        $module_id = $this->input->int('module_id', 0);
        $page_id = $this->input->int('page_id', 0);

        if(self::MODULE_ID === $module_id && 0 !== $page_id) {
            $page = new PeepSoPage($page_id);
            $profile_url = $page->get_url();
        }

        return($profile_url);
    }

    /**
     * todo:docblock
     */
    public function filter_photos_owner($clauses)
    {
        global $wpdb;

        $module_id = $this->input->int('module_id', 0);
        $page_id = $this->input->int('page_id', 0);

        if(self::MODULE_ID === $module_id && 0 !== $page_id) {
            // Filter for pages joined
            $clauses['join'] .= sprintf(' LEFT JOIN `' . $wpdb->prefix . PeepSoPageUsers::TABLE . '` `peepso_page_member` ON ' .
                ' `' . $wpdb->prefix . PeepSoPhotosModel::TABLE . '`.`pho_owner_id` = `peepso_page_member`.`pm_user_id` AND `peepso_page_member`.`pm_page_id` = %d ', $page_id) ;
        }

        return $clauses;
    }

    /**
     * todo:docblock
     */
    public function filter_photos_owner_name($owner_name)
    {
        $module_id = $this->input->int('module_id', 0);
        $page_id = $this->input->int('page_id', 0);

        if(self::MODULE_ID === $module_id && 0 !== $page_id) {
            $page = new PeepSoPage($page_id);
            $owner_name = $page->name;
        }

        return($owner_name);
    }

    public function photos_stream_photos_album($album_id)
    {
        $module_id = $this->input->int('module_id', 0);
        $page_id = $this->input->int('page_id', 0);

        if(self::MODULE_ID === $module_id && 0 !== $page_id) {
            $photo_album = new PeepSoPhotosAlbumModel();
            $album_id = $photo_album->get_photo_album_id($page_id, self::MODULE_ID . PeepSoSharePhotos::ALBUM_STREAM, 0, self::MODULE_ID);
        }

        return($album_id);
    }

    public function photos_profile_photos_album($album_id, $album)
    {
        $module_id = $this->input->int('module_id', 0);
        $page_id = $this->input->int('page_id', 0);

        if(self::MODULE_ID === $module_id && 0 !== $page_id) {
            $photo_album = new PeepSoPhotosAlbumModel();
            $album_id = $photo_album->get_photo_album_id($page_id, $album, 0, self::MODULE_ID);
        }

        return($album_id);
    }

    /**
     * Setup album for page if album for page not created yet
     * @param page_id Viewed photo page
     */
    public function action_setup_page_album($page=0)
    {
        if(!class_exists('PeepSoSharePhotos')) {
            return;
        }

        // check page_id
        if($page instanceof PeepSoPage) {
            $page_id = $page->get('id');
        } else {
            $page_id = $page;
        }

        if($page_id !== 0)
        {
            global $wpdb;

            $page_user = new PeepSoPageUser($page_id);
            $page = new PeepSoPage($page_id);
            $dir = $page->get_image_dir();
            $user_id = $page->owner_id;

            $album_model = new PeepSoPhotosAlbumModel();
            foreach($this->photo_page_system_album as $album)
            {
                $album_id = $album_model->get_photo_album_id($page_id, $album['is_system'], 0, PeepSoPagesPlugin::MODULE_ID);
                $new_album_id = $album_id;
                // if album not found, insert the album
                if(FALSE === $album_id) {
                    $data = array(
                        'pho_owner_id' => $page_id,
                        'pho_album_acc' => $album['albumname_acc'],
                        'pho_album_name' => $album['albumname'],
                        'pho_system_album' => $album['is_system'], // flag for album, 1 = system album, 2 = user created album
                        'pho_module_id' => PeepSoPagesPlugin::MODULE_ID,
                    );
                    $wpdb->insert($wpdb->prefix . PeepSoPhotosAlbumModel::TABLE , $data);

                    $new_album_id = $wpdb->insert_id;

                    // save avatars when upgrading
                    // if profile avatars album not created yet
                    if($album['is_system'] == self::MODULE_ID . PeepSoSharePhotos::ALBUM_AVATARS) {

                        $content = '';
                        $extra = array(
                            'module_id' => PeepSoSharePhotos::MODULE_ID,
                            'act_access' => PeepSo::ACCESS_PUBLIC,
                        );

                        $dest_orig = $dir . 'avatar-orig.jpg';

                        // check if file exist and post update avatar change option is true
                        if (file_exists($dest_orig)) {

                            $this->file_avatar = $dest_orig;
                            add_filter('peepso_photos_pages_avatar_original', array(&$this, 'set_file_avatar'),10,1);
                            add_filter('peepso_activity_allow_empty_content', array(&$this, 'activity_allow_empty_content'), 10, 1);
                            add_filter('peepso_pre_write_content', array(&$this, 'set_post_date'), 10, 1);
                            add_filter('peepso_pre_write_content', array(&$this, 'set_post_status'), 20, 1);
                            add_action('peepso_activity_after_add_post', array(&$this, 'after_add_post_avatar'), 10, 2);

                            $peepso_activity = PeepSoActivity::get_instance();
                            $post_id = $peepso_activity->add_post($user_id, $user_id, $content, $extra);
                            add_post_meta($post_id, PeepSoSharePhotos::POST_META_KEY_PHOTO_TYPE, PeepSoSharePhotos::POST_META_KEY_PHOTO_TYPE_AVATAR, true);
                            add_post_meta($post_id, 'peepso_page_id', $page_id);
                            add_post_meta($post_id, 'peepso_post_as_page', 1);

                            remove_filter('peepso_photos_pages_avatar_original', array(&$this, 'set_file_avatar'));
                            remove_filter('peepso_activity_allow_empty_content', array(&$this, 'activity_allow_empty_content'));
                            remove_filter('peepso_pre_write_content', array(&$this, 'set_post_date'));
                            remove_filter('peepso_pre_write_content', array(&$this, 'set_post_status'));
                            remove_action('peepso_activity_after_add_post', array(&$this, 'after_add_post_avatar'));
                        }
                    }

                    // save covers when upgrading
                    // if profile covers album not created yet
                    if($album['is_system'] == self::MODULE_ID . PeepSoSharePhotos::ALBUM_COVERS) {
                        #$content = __('change cover','picso');
                        $content = '';
                        $extra = array(
                            'module_id' => PeepSoSharePhotos::MODULE_ID,
                            'act_access' => PeepSo::ACCESS_PUBLIC,
                        );

                        $dest_file = $dir . 'cover.jpg';

                        if(file_exists($dest_file)) {
                            $this->file_cover = $dest_file;
                            add_filter('peepso_photos_pages_cover_original', array(&$this, 'set_file_cover'));
                            add_filter('peepso_activity_allow_empty_content', array(&$this, 'activity_allow_empty_content'), 10, 1);
                            add_filter('peepso_pre_write_content', array(&$this, 'set_post_date'), 10, 1);
                            add_filter('peepso_pre_write_content', array(&$this, 'set_post_status'), 20, 1);
                            add_action('peepso_activity_after_add_post', array(&$this, 'action_add_post_cover'), 10, 2);

                            $peepso_activity = PeepSoActivity::get_instance();
                            $post_id = $peepso_activity->add_post($user_id, $user_id, $content, $extra);
                            add_post_meta($post_id, PeepSoSharePhotos::POST_META_KEY_PHOTO_TYPE, PeepSoSharePhotos::POST_META_KEY_PHOTO_TYPE_COVER, true);
                            add_post_meta($post_id, 'peepso_page_id', $page_id);
                            add_post_meta($post_id, 'peepso_post_as_page', 1);

                            remove_filter('peepso_photos_pages_cover_original', array(&$this, 'set_file_cover'));
                            remove_filter('peepso_activity_allow_empty_content', array(&$this, 'activity_allow_empty_content'));
                            remove_filter('peepso_pre_write_content', array(&$this, 'set_post_date'));
                            remove_filter('peepso_pre_write_content', array(&$this, 'set_post_status'));
                            remove_action('peepso_activity_after_add_post', array(&$this, 'action_add_post_cover'));
                        }
                    }
                }
                if($album['is_system'] == self::MODULE_ID . PeepSoSharePhotos::ALBUM_STREAM) {
                    $wpdb->update(
                        $wpdb->prefix . PeepSoPhotosModel::TABLE,
                        array(
                            'pho_album_id' => $new_album_id,    // int (number)
                        ),
                        array( 'pho_owner_id' => $page_id, 'pho_album_id' => 0, 'pho_module_id' => PeepSoPagesPlugin::MODULE_ID ), // where photo_album_id still undefined (0)
                        array( '%d' ),
                        array( '%d','%d' )
                    );
                }
            }
        }
    }

    /**
     * Set file cover
     */
    function set_file_cover($file)
    {
        if(!empty($this->file_cover))
        {
            $file = $this->file_cover;
        }
        return ($file);
    }

    /**
     * This function manipulates the image/photo uploaded including uploading to Amazon S3
     * @param int $post_id The post ID
     * @param int $act_id The activity ID
     */
    public function action_add_post_cover($post_id, $act_id)
    {
        $file = '';
        $file = apply_filters('peepso_photos_pages_cover_original',$file);
        $album = apply_filters('peepso_photos_pages_covers_album', self::MODULE_ID . PeepSoSharePhotos::ALBUM_COVERS);
        if(!empty($file)) {
            $_photos_model = new PeepSoPhotosModel;
            $_photos_model->save_images_profile($file, $post_id, $act_id, $album);
        }
    }

    /**
     * Set file avatar
     */
    function set_file_avatar($file)
    {
        if(!empty($this->file_avatar))
        {
            $file = $this->file_avatar;
        }
        return ($file);
    }

    /**
     * This function manipulates the image/photo uploaded including uploading to Amazon S3
     * @param int $post_id The post ID
     * @param int $act_id The activity ID
     */
    public function after_add_post_avatar($post_id, $act_id)
    {
        $file   = '';
        $file   = apply_filters('peepso_photos_pages_avatar_original',$file);
        $album  = apply_filters('peepso_photos_pages_avatars_album', self::MODULE_ID . PeepSoSharePhotos::ALBUM_AVATARS);
        if(!empty($file)) {
            $_photos_model = new PeepSoPhotosModel;
            $_photos_model->save_images_profile($file, $post_id, $act_id, $album);
        }
    }

    /**
     * Checks if empty content is allowed
     * @param string $allowed
     * @return boolean always returns TRUE
     */
    public function activity_allow_empty_content($allowed)
    {
        /*$type = $input->value('type', '', array('photo', 'album')); // SQL safe
        if ('photo' === $type || 'album' === $type) {
            $allowed = TRUE;
        }*/

        if(isset($this->file_avatar) || isset($this->file_cover) ) {
            $allowed = TRUE;
        }

        // allowed empty content after adding activity change avatar
        // SQL safe, WP sanitizes it
        if (FALSE !== wp_verify_nonce($this->input->value('_wpnonce','',FALSE), 'cover-photo')) {
            $allowed = TRUE;
        }

        // allowed empty content after adding activity change cover
        // if (isset($_GET['cover'])) {
        //     $allowed = TRUE;
        // }

        return ($allowed);
    }

    /**
     * Set post date for change avatar/cover activities
     * @param array $aPostData
     * @return array $aPostData
     */
    public function set_post_date($aPostData) {

        if(!empty($this->file_avatar))
        {
            $filename = $this->file_avatar;
        }

        if(!empty($this->file_cover))
        {
            $filename = $this->file_cover;
        }

        if(is_array($aPostData)) {
            $post_date = date( 'Y-m-d H:i:s', current_time( 'timestamp'));
            $post_date_gmt = date( 'Y-m-d H:i:s', current_time( 'timestamp', 1 ));
            $aPostData['post_date'] = $post_date;
            $aPostData['post_date_gmt'] = $post_date_gmt;
        }

        return $aPostData;
    }

    /**
     * Set post status for change avatar/cover activities
     * @param array $aPostData
     * @return array $aPostData
     */
    public function set_post_status($aPostData) {

        $page_id = $this->input->int('page_id', 0);

        if(0 !== $page_id) {

            $page = new PeepSoPage($page_id);

            if(is_array($aPostData) && (isset($aPostData['page_avatar']) && 0 === intval(PeepSo::get_option('photos_pages_enable_post_updates_page_avatar',1)) || FALSE === $page->published)) {
                $aPostData['post_status'] = 'pending';
            }

            if(is_array($aPostData) && (isset($aPostData['page_cover']) && 0 === intval(PeepSo::get_option('photos_pages_enable_post_updates_page_cover',1))  || FALSE === $page->published)) {
                $aPostData['post_status'] = 'pending';
            }
        }

        return $aPostData;
    }

    /**
     * Function called after avatar changed
     * @param user_id
     * @param dest_thumb
     * @param dest_full
     * @param dest_orig
     */
    public function action_change_avatar($page_id, $dest_thumb, $dest_full, $dest_orig)
    {
        if(0 !== $page_id){

            // migrate from activate function,
            // setup album before uploading avatar
            $this->action_setup_page_album($page_id);

            #$content = __('change avatar','picso');
            $content = '';
            $extra = array(
                'module_id' => PeepSoSharePhotos::MODULE_ID,
                'act_access' => PeepSo::ACCESS_PUBLIC,
                'page_avatar' => TRUE
            );
            $user_id = get_current_user_id();

            $this->file_avatar = $dest_orig;
            add_filter('peepso_photos_pages_avatar_original', array(&$this, 'set_file_avatar'));
            add_action('peepso_activity_after_add_post', array(&$this, 'after_add_post_avatar'), 10, 2);
            add_filter('peepso_pre_write_content', array(&$this, 'set_post_date'), 10, 1);
            add_filter('peepso_pre_write_content', array(&$this, 'set_post_status'), 20, 1);

            $peepso_activity = PeepSoActivity::get_instance();
            $post_id = $peepso_activity->add_post($user_id, $user_id, $content, $extra);
            add_post_meta($post_id, PeepSoSharePhotos::POST_META_KEY_PHOTO_TYPE, PeepSoSharePhotos::POST_META_KEY_PHOTO_TYPE_AVATAR, true);
            add_post_meta($post_id, 'peepso_page_id', $page_id);
            add_post_meta($post_id, 'peepso_post_as_page', 1);
        }
    }

    /**
     * Function called after cover changed
     * @param user_id
     * @param dest_file
     */
    public function action_change_cover($page_id, $dest_file)
    {
        if(0 !== $page_id){

            // migrate from activate function,
            // setup album before uploading cover
            $this->action_setup_page_album($page_id);

            #$content = __('change cover','picso');
            $content = '';
            $extra = array(
                'module_id' => PeepSoSharePhotos::MODULE_ID,
                'act_access' => PeepSo::ACCESS_PUBLIC,
                'page_cover' => TRUE
            );
            $user_id = get_current_user_id();

            $this->file_cover = $dest_file;
            add_filter('peepso_photos_pages_cover_original', array(&$this, 'set_file_cover'));
            add_action('peepso_activity_after_add_post', array(&$this, 'action_add_post_cover'), 10, 2);
            add_filter('peepso_pre_write_content', array(&$this, 'set_post_date'), 10, 1);
            add_filter('peepso_pre_write_content', array(&$this, 'set_post_status'), 20, 1);

            $peepso_activity = PeepSoActivity::get_instance();
            $post_id = $peepso_activity->add_post($user_id, $user_id, $content, $extra);
            add_post_meta($post_id, PeepSoSharePhotos::POST_META_KEY_PHOTO_TYPE, PeepSoSharePhotos::POST_META_KEY_PHOTO_TYPE_COVER, true);
            add_post_meta($post_id, 'peepso_page_id', $page_id);
            add_post_meta($post_id, 'peepso_post_as_page', 1);
        }
    }

    public function stream_action_change_avatar($action, $post_id) {
        $page_id = get_post_meta($post_id, 'peepso_page_id', TRUE);

        if(!empty($page_id)) {
            $action = __(' updated page avatar', 'pageso');
        }

        return ($action);
    }

    public function stream_action_change_cover($action, $post_id) {
        $page_id = get_post_meta($post_id, 'peepso_page_id', TRUE);

        if(!empty($page_id)) {
            $action = __(' updated page cover', 'pageso');
        }

        return ($action);
    }

    public function photos_stream_action_photo_album($action, $post_id) {
        $page_id = get_post_meta($post_id, 'peepso_page_id', TRUE);

        if(!empty($page_id)) {
            $photos_album_model = new PeepSoPhotosAlbumModel();

            // [USER] added [photo/photos] to [ALBUM NAME] album
            $total_photos = get_post_meta($post_id, PeepSoSharePhotos::POST_META_KEY_PHOTO_COUNT, true);
            $album = $photos_album_model->get_photo_album($page_id, 0, $post_id, self::MODULE_ID);

            // generate link
            $page = new PeepSoPage($page_id);
            $link_to_album = $page->get_url() . 'photos/album/' . $album[0]->pho_album_id;

            $action = sprintf(_n(' added %1$d photo to the album: <a href="%3$s">%2$s</a>', ' added %1$d photos to the album: <a href="%3$s">%2$s</a>', $total_photos, 'picso'), $total_photos, $album[0]->pho_album_name, $link_to_album);
        }

        return ($action);
    }

    public function stream_action_album($action, $post_id) {
        $page_id = get_post_meta($post_id, 'peepso_page_id', TRUE);

        if(!empty($page_id)) {
            $action = __(' updated page cover', 'pageso');
        }

        return ($action);
    }

    /* * * * VIDEOS * * * * */

    /**
     * Modify the clauses to filter posts
     * @param  array $clauses
     * @param  int $user_id The owner of the activity stream
     * @return array
     */
    public function filter_videos_post_clauses($clauses, $module_id, $widgets)
    {
        global $wpdb;

        if($module_id == self::MODULE_ID) {

            $page_id = $this->input->int('page_id', 0);

            // filter clauses for videos
        }

        if($widgets) {
            // Filter for pages joined
            $clauses['join'] .= ' LEFT JOIN `' . $wpdb->postmeta  . '` `peepso_page_meta` ON ' .
                ' `' . $wpdb->posts . '`.`ID` = `peepso_page_meta`.`post_id` AND `peepso_page_meta`.`meta_key` = \'peepso_page_id\' ' ;

            $clauses['where'] .= " AND (`peepso_page_meta`.`meta_value` IS NULL) ";
        }

        return $clauses;
    }

    /**
     * todo:docblock
     */
    public function filter_videos_owner_name($owner_name)
    {
        $module_id = $this->input->int('module_id', 0);
        $page_id = $this->input->int('page_id', 0);

        if(self::MODULE_ID === $module_id && 0 !== $page_id) {
            $page = new PeepSoPage($page_id);
            $owner_name = $page->name;
        }

        return($owner_name);
    }

    /**
     * todo:docblock
     */
    public function filter_videos_owner($clauses)
    {
        global $wpdb;

        $module_id = $this->input->int('module_id', 0);
        $page_id = $this->input->int('page_id', 0);

        if(self::MODULE_ID === $module_id && 0 !== $page_id) {
            // Filter for pages joined
            $clauses['join'] .= ' LEFT JOIN `' . $wpdb->postmeta . '` `peepso_page_member` ON ' .
                ' `' . $wpdb->posts . '`.`ID` = `peepso_page_member`.`post_id`' ;
            $clauses['where'] .= sprintf(' AND (`peepso_page_member`.`meta_key`=\'peepso_page_id\' AND `peepso_page_member`.`meta_value` = %d)', $page_id);
        }

        return $clauses;
    }

    /* * * * Notifications * * * */

    /**
     * Notify page OWNERS about a new member (if enabled)
     * Create a stream entry (if applicable)
     * Make sure "followers" entry is created
     * @param $page_id
     * @param $user_id
     */
    public function action_page_user_join($page_id, $user_id)
    {
        $PeepSoPage = new PeepSoPage($page_id);

        // Notify Owners and Managers
        if(!$PeepSoPage->is_join_muted) {
            $PeepSoPageUsers = new PeepSoPageUsers($page_id);
            $owners_and_managers = $PeepSoPageUsers->get_owners_and_managers();

            $PeepSoNotifications = new PeepSoNotifications();

            $i18n = __('liked your page', 'pageso');
            $message = 'liked your page';
            $args = ['pageso'];

            foreach ($owners_and_managers as $PeepSoPageUser) {
                $PeepSoNotifications->add_notification_new($user_id, $PeepSoPageUser->user_id, $message, $args, 'pages_user_join', self::MODULE_ID, $page_id);
            }
        }


        // Post to stream
        if(1 == PeepSo::get_option('pages_join_post_to_stream',0)) {
            // POST TO STREAM
            $extra = array(
                'module_id' => self::MODULE_ID,
                'act_access'=> PeepSo::ACCESS_MEMBERS,
            );

            $content = self::JOIN_STREAM;

            // create an activity item
            $act = PeepSoActivity::get_instance();
            $act_id = $act->add_post($user_id, $user_id, $content, $extra);

            update_post_meta($act_id, 'peepso_page_id', $page_id);
            update_post_meta($act_id, 'peepso_post_as_page', true);
            update_post_meta($act_id, self::JOIN_STREAM_META, $page_id);
        }

        // Make sure a "followers" record is created
        $PeepSoPageFollower = new PeepSoPageFollower($page_id, $user_id);
    }

    /**
     * Notify the INVITATION SENDER that the invited user accepted
     * @param $page_id
     */
    public function action_page_user_invitation_accept(PeepSoPageUser $PeepSoPageUser)
    {
        if($PeepSoPageUser->invited_by_id) {
            $PeepSoNotifications = new PeepSoNotifications();

            $i18n = __('accepted your page invitation', 'pageso');
            $message = 'accepted your page invitation';
            $args = ['pageso'];

            $PeepSoNotifications->add_notification_new(get_current_user_id(), $PeepSoPageUser->invited_by_id, $message, $args, 'pages_user_invitation_accept', self::MODULE_ID, $PeepSoPageUser->page_id);
        }
    }

    /**
     * Notify the page OWNERS AND MANAGERS  about a new join request
     * @param $page_id
     */
    public function action_page_user_join_request_send($page_id) {

        // delete all join_request_send notifications for this page
        global $wpdb;

        $where = array(
            'not_type' 			=> 'pages_user_join_request_send',
            'not_external_id'	=> $page_id,
        );

        $wpdb->delete($wpdb->prefix.PeepSoNotifications::TABLE, $where);

        $PeepSoPageUsers = new PeepSoPageUsers($page_id);

        // aggregated notification textp
        $args = ['pageso'];
        $message = $this->notification_message_user_join_request($PeepSoPageUsers, get_current_user_id(), $args);

        $owners_and_managers = $PeepSoPageUsers->get_owners_and_managers();

        $PeepSoNotifications = new PeepSoNotifications();

        foreach($owners_and_managers as $PeepSoPageUser) {
            $PeepSoNotifications->add_notification_new(get_current_user_id(), $PeepSoPageUser->user_id, $message, $args, 'pages_user_join_request_send', self::MODULE_ID, $page_id);
        }
    }

    private function notification_message_user_join_request(PeepSoPageUsers $PeepSoPageUsers, $user_id, &$args = [])
    {

        $pending = $PeepSoPageUsers->get_pending_admin();
        $pending_count = count($pending) -1; // exclude self

        if($pending_count > 0) {

            $args[] = '<strong>';

            if ($pending_count == 1) {
                foreach ($pending as $PeepSoPageUser) {

                    if ($PeepSoPageUser->user_id == $user_id) { continue; }

                    $i18m = __('and %s%s%s requested to join your page', 'pageso');
                    $message = 'and %s%s%s requested to join your page';

                    $PeepSoUser = PeepSoUser::get_instance($PeepSoPageUser->user_id);
                    $args[] = $PeepSoUser->get_firstname();
                }
            } else {
                $i18m = __('and %s%s more users%s requested to join your page', 'pageso');
                $message = 'and %s%s more users%s requested to join your page';
                $args[] = $pending_count;
            }

            $args[] = '</strong>';
        } else {
            $i18n = __('requested to join your page', 'pageso');
            $message = 'requested to join your page';
        }


        return $message;
    }

    private function notification_update_user_join_request($page_id, $user_id)
    {
        global $wpdb;

        $PeepSoPageUsers = new PeepSoPageUsers($page_id, $user_id);
        $pending = $PeepSoPageUsers->get_pending_admin();

        $where = array(
            'not_type' => 'pages_user_join_request_send',
            'not_external_id' => $page_id,
        );

        if(count($pending)) {
            // new not_from_user_id (in case we accept or reject the user who is the current not_from_user_id)
            foreach ($pending as $PeepSoPageUser) {
                $data = array('not_from_user_id' => $PeepSoPageUser->user_id);
                break;
            }

            // need new aggregated notification content
            $data['not_message'] = $this->notification_message_user_join_request($PeepSoPageUsers, $data['not_from_user_id']);

            $wpdb->update($wpdb->prefix . PeepSoNotifications::TABLE, $data, $where);
        } else {
            $wpdb->delete($wpdb->prefix . PeepSoNotifications::TABLE, $where);
        }
    }

    /**
     * Notify USER WHO REQUESTED that he was accepted
     * @param $page_id
     * @param $user_id
     */
    public function action_page_user_join_request_accept($page_id, $user_id)
    {
        $this->notification_update_user_join_request($page_id, $user_id);
        $PeepSoNotifications = new PeepSoNotifications();

        $i18n = __('accepted you as a page member', 'pageso');
        $message = 'accepted you as a page member';
        $args = ['pageso'];

        $PeepSoNotifications->add_notification_new(get_current_user_id(), $user_id, $message, $args, 'pages_user_join_request_accept', self::MODULE_ID, $page_id);

        // Make sure a "followers" record is created
        $PeepSoPageFollower = new PeepSoPageFollower($page_id, $user_id);
    }


    /**
     * Clean up after user deletion
     * @param $page_id
     * @param $user_id
     */
    public function action_page_user_delete($page_id, $user_id)
    {
        // if the user was pending_admin, update the notifications
        $this->notification_update_user_join_request($page_id, $user_id);

        // delete the "user has joined" posts
        $args = array(
            'author' => $user_id,
            'post_type' => 'peepso-post',
            'meta_query' => array(
                array(
                    'key' => self::JOIN_STREAM_META,
                    'value' => $page_id,
                    'compare' => '=',
                ),
            )
        );

        $posts = get_posts($args);
        if(count($posts)) {
            foreach($posts as $post) {
                wp_delete_post($post->ID);
            }
        }

        // unsubscribe
        $PeepSoPageFollower = new PeepSoPageFollower($page_id, $user_id);
        $PeepSoPageFollower->delete();
    }

    public function action_page_rename($page_id, $user_id) {
        $page_users = new PeepSoPageUsers($page_id);
        $list_members = $page_users->get_members();
        $page = new PeepSoPage($page_id);

        $i18n = __('renamed a page you like', 'pageso');
        $message = 'renamed a page you like';
        $args = ['pageso'];

        if(count($list_members) > 0) {
            $notif = new PeepSoNotifications();

            foreach($list_members as $pageuser) {
                if ($pageuser->user_id != $user_id) {
                    $notif->add_notification_new($user_id, $pageuser->user_id, $message, $args, 'pages_rename', self::MODULE_ID, $page_id);
                }
            }
        }
    }

    public function action_page_privacy_change($page_id, $user_id) {
        $page_users = new PeepSoPageUsers($page_id);
        $page = new PeepSoPage($page_id);

        $list_members = $page_users->get_members();

        if(count($list_members ) > 0) {
            $notif = new PeepSoNotifications();

            $i18n = __('changed page privacy to %s', 'pageso');
            $message = 'changed page privacy to %s';
            $args = [
                    'pageso',

                    $page->privacy['notif']
            ];

            foreach($list_members  as $pageuser) {
                if ($pageuser->user_id != $user_id) {
                    $notif->add_notification_new($user_id, $pageuser->user_id, $message, $args, 'pages_privacy_change', self::MODULE_ID, $page_id);
                }
            }
        }
    }

    public function action_activity_single_override_header()
    {
        global $post;

        // Page found
        $PeepSoActivityShortcode = PeepSoActivityShortcode::get_instance();
        if( $PeepSoActivityShortcode->is_permalink_page()) {
            $page_id = get_post_meta($post->ID, 'peepso_page_id', true);

            // page not found
            if(!get_post($page_id)) {
                PeepSo::redirect(PeepSo::get_page('pages').'?'.$page_id.'/');
                die();
            }

            // page found
            if($page_id) {
                $page = new PeepSoPage($page_id);
                PeepSoTemplate::exec_template('pages', 'page-header', array('page'=>$page, 'page_segment'=> 'stream'));
                $this->enqueue_scripts(TRUE);
            }
        }
    }

    /* * * * Frontend utils * * * */

    public  function admin_enqueue_scripts()
    {
        wp_enqueue_script('peepso-admin-pages',
            PeepSo::get_asset('js/admin.js', __FILE__),
            array('jquery', 'underscore'), self::PLUGIN_VERSION, TRUE);
    }

    public  function enqueue_scripts()
    {
        global $post;

        $dialog_invite_params = array();
        $page_id = NULL;

        // Get page_id from single post view.
        $as = PeepSoActivityShortcode::get_instance();
        if ($as->is_permalink_page()) {
            $page_id = get_post_meta($post->ID, 'peepso_page_id', true);
            if ($page_id) {
                $page = new PeepSoPage($page_id);
                $page_id = $page->get('id');
            }
        }
        // Or from the URL.
        else {
            $url_segments = PeepSoUrlSegments::get_instance();
            $segment_part = is_front_page() ? 0 : 1;
            if (PeepSoUrlSegments::get_instance()->_shortcode == 'peepso_pages') {
                $page_id = $url_segments->get($segment_part);

                if ($page_id == 'peepso_pages') {
                    $page_id = $url_segments->get($segment_part+1);
                }
            }
            if ($page_id) {
                $page = new PeepSoPage($page_id);
                $page_id = $page->get('id');

                // Reload on close dialog invite if currently on "GROUP_ID/members/invited" page.
                $reload_on_close = false;
                $segment_part_1 = $url_segments->get($segment_part + 1);
                $segment_part_2 = $url_segments->get($segment_part + 2);
                if ('members' === $segment_part_1 && 'invited' === $segment_part_2) {
                    $reload_on_close = true;
                }

                $dialog_invite_params = array( 'reload_on_close' => $reload_on_close );
            }
        }

        $PeepSoPageUser = new PeepSoPageUser($page_id, get_current_user_id());

        $pin_page_only = 1 == PeepSo::get_option('pages_pin_page_only', 0);
        $pin_page_only_no_pinned_style = 1 == PeepSo::get_option('pages_pin_page_only_no_pinned_style', 0);
        $pin_page_only_no_pinned_style = $pin_page_only && $pin_page_only_no_pinned_style;

        $data = array(
            'dialogCreateTemplate' => PeepSoTemplate::exec_template('pages', 'dialog-create', NULL, TRUE),
            'dialogInviteTemplate' => PeepSoTemplate::exec_template('pages', 'dialog-invite', $dialog_invite_params, TRUE),
            'listItemTemplate' => PeepSoTemplate::exec_template('pages', 'pages-item', NULL, TRUE),
            'listItemMemberActionsTemplate' => PeepSoTemplate::exec_template('pages', 'pages-item-member-actions', NULL, TRUE),
            'listCategoriesTemplate' => PeepSoTemplate::exec_template('pages', 'pages-categories', NULL, TRUE),
            'headerActionsTemplate' => PeepSoTemplate::exec_template('pages', 'page-header-actions', NULL, TRUE),
            'memberItemTemplate' => PeepSoTemplate::exec_template('pages', 'page-members-item', NULL, TRUE),
            'memberItemActionsTemplate' => PeepSoTemplate::exec_template('pages', 'page-members-item-actions', NULL, TRUE),
            'page_url' => PeepSo::get_page('pages') . '?category=##category_id##',
            'page_id' => $page_id,
            'user_id' => get_current_user_id(),
            'max_categories' => PeepSo::get_option_new('pages_categories_multiple_max'),
            'pin_page_only' => $pin_page_only ? 1 : 0,
            'pin_page_only_no_pinned_style' => $pin_page_only_no_pinned_style ? 1 : 0,
            'force_posts_in_pages' => apply_filters('peepso_filter_force_posts_in_pages', FALSE),
            'peepsoPageUser' => array(
                'can_manage_users' => $PeepSoPageUser->can('manage_users'),
                'can_pin_posts' => $PeepSoPageUser->can('pin_posts'),
            ),
            'module_id' => self::MODULE_ID,
            'list_show_owner' => PeepSo::get_option('pages_listing_show_page_owner', 0),
            'lang' => array(
                'more' => __('More', 'pageso'),
                'less' => __('Less', 'pageso'),
                'member' => __('follower', 'pageso'),
                'members' => __('followers', 'pageso'),
                'name_change_confirmation' => __('Are you sure you want to change the page name?','pageso') .'<br>' . __('All page members will be notified.','pageso'),
                'slug_change_confirmation' => __('Are you sure you want to change the page URL?','pageso') .'<br>' . __('All page members will be notified.','pageso'),
                'privacy_change_confirmation' => __('Are you sure you want to change the page privacy?','pageso') .'<br>' . __('All page members will be notified.','pageso'),
                'uncategorized' => __('Uncategorized', 'pageso'),
            ),

            // set nonce
            'nonce_set_page_name' => wp_create_nonce('set-page-name'),
            'nonce_set_page_slug' => wp_create_nonce('set-page-slug'),
            'nonce_set_page_privacy' => wp_create_nonce('set-page-privacy'),
            'nonce_set_page_description' => wp_create_nonce('set-page-description'),
            'nonce_set_page_categories' => wp_create_nonce('set-page-categories'),
            'nonce_set_page_property' => wp_create_nonce('set-page-property'),
            'nonce_set_page_custom_input' => wp_create_nonce('set-page-custom-input'),
        );

        // get page info
        if ($page_id) {

            if ($page->id)	{
                $data['id'] = $page->get('id');
                $data['name'] = $page->get('name');
                $data['hasAvatar'] = $page->has_avatar() ? TRUE : FALSE;
                $data['imgAvatar'] = $page->get_avatar_url();
                $data['imgOriginal'] = $page->get_avatar_url_orig();
                $data['privacy'] = $page->privacy['id'];
            }
        }

        wp_localize_script('peepso', 'peepsopagesdata', $data);

        // Single page page's script.
        if ($page_id) {
            wp_enqueue_script('peepso-pages-page-page',
                PeepSo::get_asset('js/page-page.min.js', __FILE__),
                array('jquery-ui-draggable', 'peepso', 'peepso-fileupload'), self::PLUGIN_VERSION, TRUE);

            add_filter('peepso_data', function( $data ) use ( $page_id, $page ) {
                if ($page_id) {
                    $page_data = array(
                        'id'                  => $page->get('id'),
                        'module_id'           => self::MODULE_ID,
                        'slug'                => $page->get('slug'),
                        'name'                => $page->get('name'),
                        'has_avatar'          => $page->has_avatar() ? TRUE : FALSE,
                        'img_avatar'          => $page->get_avatar_url(),
                        'img_avatar_default'  => $page->get_default_avatar_url(),
                        'img_avatar_original' => $page->get_avatar_url_orig(),
                        'avatar_nonce'        => wp_create_nonce('page-avatar'),
                        'has_cover'           => $page->has_cover() ? TRUE : FALSE,
                        'img_cover'           => $page->get_cover_url(),
                        'img_cover_default'   => $page->get_cover_default(),
                        'cover_nonce'         => wp_create_nonce('page-cover'),
                        'text_error_filetype' => __('The file type you uploaded is not allowed. Only JPEG, PNG, and WEBP allowed.', 'pageso'),
                        'text_error_filesize' => sprintf(
                            __('The file size you uploaded is too big. The maximum file size is %s.', 'pageso'),
                            '<strong>' . PeepSoGeneral::get_instance()->upload_size() . '</strong>'
                        )
                    );

                    $data['page'] = array_merge(
                        $page_data,
                        array(
                            'template_avatar'       => PeepSoTemplate::exec_template('pages', 'dialog-avatar', array( 'data' => $page_data ), TRUE),
                            'template_cover_remove' => PeepSoTemplate::exec_template('pages', 'dialog-cover-remove', array(), TRUE),
                        )
                    );
                }

                return $data;
            }, 10, 1);
        }

        $logged_in = is_user_logged_in();

        wp_enqueue_style('peepso-fileupload');
        wp_enqueue_script('peepso-fileupload');

        wp_register_script('peepso-pages-create',
            $logged_in ? PeepSo::get_asset('js/pages-create.min.js', __FILE__) : FALSE,
            array('peepso'), PeepSo::PLUGIN_VERSION, TRUE);

        wp_register_script('peepso-pages-crop',
            $logged_in ? PeepSo::get_asset('js/crop.min.js') : FALSE,
            array('jquery', 'peepso-hammer'), PeepSo::PLUGIN_VERSION, TRUE);

        wp_register_script('peepso-pages-dialog-invite',
            $logged_in ? PeepSo::get_asset('js/dialog-invite.min.js', __FILE__) : FALSE,
            array('jquery', 'peepso'), PeepSo::PLUGIN_VERSION, TRUE);

        wp_enqueue_script('peepso-pages-page',
            PeepSo::get_asset('js/page.min.js', __FILE__),
            array('peepso', 'peepso-pages-dialog-invite'), PeepSo::PLUGIN_VERSION, TRUE);

        wp_enqueue_script('peepso-pages',
            PeepSo::get_asset('js/bundle.min.js', __FILE__),
            array('peepso-bundle', 'peepso-page-autoload', 'peepso-pages-create'), self::PLUGIN_VERSION, TRUE);

        add_filter('peepso_data', function( $data ) {
            $data['pages'] = array(
                'textNoResult' => __('No result found.', 'pageso'),
                'categories' => array(
                    'pages_categories_expand_all' => PeepSo::get_option('pages_categories_expand_all', 0),
                    'pages_categories_page_count' => PeepSo::get_option('pages_categories_page_count', 4)
                )
            );
            return $data;
        }, 10, 1 );
    }

    /**
     * todo
     */
    public function notifications_activity_type($activity_type, $post_id, $act_id = NULL) {

        # $activity_type = array(
        #   'text' => __('post', 'peepso'),
        #   'type' => 'post'
        # );

        /**
         * Please note that we mus define email template for each
         * 1. like_{type}
         * 2. user_comment_{type}
         * 3. share_{type}
         */

        if(!class_exists('PeepSoSharePhotos')) {
            return $activity_type;
        }

        $page_id = get_post_meta($post_id, 'peepso_page_id', TRUE);

        if(is_array($activity_type) && !empty($page_id)) {
            $photo_type = get_post_meta($post_id, PeepSoSharePhotos::POST_META_KEY_PHOTO_TYPE, true);

            $type = '';
            if(in_array($activity_type['type'], array('user_comment', 'share'))) {
                $type = $activity_type['type'] . '_';
            } elseif(in_array($activity_type['type'], array('user_comment_cover', 'user_comment_avatar'))) {
                $type = 'user_comment_page_';
            }

            if( $photo_type === PeepSoSharePhotos::POST_META_KEY_PHOTO_TYPE_AVATAR ) {
                $activity_type = array(
                    'text' => __('page avatar', 'pageso'),
                    'type' => $type . 'avatar'
                );
            } else if( $photo_type === PeepSoSharePhotos::POST_META_KEY_PHOTO_TYPE_COVER ) {
                $activity_type = array(
                    'text' => __('page cover photo', 'pageso'),
                    'type' => $type . 'cover'
                );
            }
        }

        return ($activity_type);
    }

    /* * * * Activation, PeepSo detection / version compatibility, licensing * * * */

    /**
     * Plugin activation.
     * Check PeepSo
     * Run installation
     * @return bool
     */
    public function activate()
    {
        if (!$this->peepso_check()) {
            return (FALSE);
        }

        require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'install' . DIRECTORY_SEPARATOR . 'activate.php');
        $install = new PeepSoPagesInstall();
        $res = $install->plugin_activation();
        if (FALSE === $res) {
            // error during installation - disable
            deactivate_plugins(plugin_basename(__FILE__));
        }

        return (TRUE);
    }

    /**
     * Check if PeepSo class is present (ie the PeepSo plugin is installed and activated)
     * If there is no PeepSo, immediately disable the plugin and display a warning
     * Run license and new version checks against PeepSo.com
     * @return bool
     */
    public function peepso_check()
    {
        if (!class_exists('PeepSo')) {
            add_action('admin_notices', function(){
                ?>
                <div class="error peepso">
                    <strong>
                        <?php echo sprintf(__('The %s plugin requires the PeepSo plugin to be installed and activated.', 'pageso'), self::PLUGIN_NAME);?>
                        <a href="plugin-install.php?tab=plugin-information&plugin=peepso-core&TB_iframe=true&width=772&height=291" class="thickbox">
                            <?php echo __('Get it now!', 'pageso');?>
                        </a>
                    </strong>
                </div>
                <?php
            });
            unset($_GET['activate']);
            deactivate_plugins(plugin_basename(__FILE__));
            return (FALSE);
        }

        // PeepSo.com license check
        if (!PeepSoLicense::check_license(self::PLUGIN_EDD, self::PLUGIN_SLUG)) {
            add_action('admin_notices', function(){
                PeepSo::license_notice(self::PLUGIN_NAME, self::PLUGIN_SLUG);
            });
        }

        if (isset($_GET['page']) && 'peepso_config' == $_GET['page'] && !isset($_GET['tab'])) {
            add_action('admin_notices', function(){
                PeepSo::license_notice(self::PLUGIN_NAME, self::PLUGIN_SLUG, true);
            });
        }

        // PeepSo.com new version check
        // since 1.7.6
        if(method_exists('PeepSoLicense', 'check_updates_new')) {
            PeepSoLicense::check_updates_new(self::PLUGIN_EDD, self::PLUGIN_SLUG, self::PLUGIN_VERSION, __FILE__);
        }

        return (TRUE);
    }

    /* * * * PeepSo admin section * * * */

    public function filter_taggable($taggable, $act_id) {
        $profile = PeepSoActivity::get_instance();

        if (!is_null($act_id) && FALSE === is_null($activity = $profile->get_activity_post($act_id))) {
            $post_id = $activity->ID;
            if ($activity->post_type == PeepSoActivityStream::CPT_COMMENT) {
                $parent_activity = $profile->get_activity_data($activity->act_comment_object_id, $activity->act_comment_module_id);

                if (is_object($parent_activity)) {
                    $parent_post = $profile->get_activity_post($parent_activity->act_id);
                    $parent_id = $parent_post->act_external_id;

                    // check if parent post is a comment
                    if($parent_post->post_type == 'peepso-comment') {
                        $comment_activity = $profile->get_activity_data($activity->act_comment_object_id, $activity->act_comment_module_id);
                        $post_activity = $profile->get_activity_data($comment_activity->act_comment_object_id, $comment_activity->act_comment_module_id);

                        $parent_comment = $profile->get_activity_post($comment_activity->act_id);
                        $parent_post = $profile->get_activity_post($post_activity->act_id);
                    }
                    $post_id = $parent_post->ID;
                }
                // $parent_activity = PeepSoActivity::get_instance();
                // $parent_activity_data = $parent_activity->get_activity_data($activity->act_comment_object_id);
                // $parent_post = $parent_activity->get_activity_post($parent_activity_data->act_id);
            }

            // check if page post single activity
            $page_id = get_post_meta($post_id, 'peepso_page_id', true);
        } else {
            // check if page page
            $module_id = $this->input->int('module_id', 0);
            if($module_id == self::MODULE_ID) {
                $page_id = $this->input->int('page_id', 0);
            }

        }

        if (isset($page_id) && $page_id > 0) {
            $page = new PeepSoPage($page_id);
            $page_users = new PeepSoPageUsers($page_id);
            $list_members = $page_users->get_members();

            // $taggable['page' . $page_id] = array(
            //     'id' => 'page' . $page_id,
            //     'name' => trim(strip_tags($page->get('name'))),
            //     'avatar' => $page->get_avatar_url(),
            //     'icon' => $page->get_avatar_url(),
            //     'type' => 'page'
            // );

            if(count($list_members) > 0) {
                foreach($list_members as $pageuser) {

                    if ($pageuser->user_id == get_current_user_id() || in_array($pageuser->user_id, $taggable)) {
                        continue;
                    }

                    $user = PeepSoUser::get_instance($pageuser->user_id);

                    $taggable[$pageuser->user_id] = array(
                        'id' => $pageuser->user_id,
                        'name' => trim(strip_tags($user->get_fullname())),
                        'avatar' => $user->get_avatar(),
                        'icon' => $user->get_avatar(),
                        'type' => 'page_member'
                    );
                }
            }
        }

        // Make all pages taggable.
        $PeepSoPages = new PeepSoPages();
        $order_by = PeepSo::get_option('pages_default_sorting', 'id');
        $order = PeepSo::get_option('pages_default_sorting_order', 'DESC');
        $pages = $PeepSoPages->get_pages(0, 100, $order_by, $order, '');

        if (count($pages) > 0) {
            foreach ($pages as $page) {
                $page_id = $page->get('id');
                $taggable['page' . $page_id] = array(
                    'id' => 'page' . $page_id,
                    'name' => trim(strip_tags($page->get('name'))),
                    'avatar' => $page->get_avatar_url(),
                    'icon' => $page->get_avatar_url(),
                    'type' => 'page'
                );
            }
        }

        return $taggable;
    }

    /**
     * todo:docblock
     */
    public function filter_check_opengraph($activity) {
        // check if activity is page activity
        $page_id = get_post_meta($activity->ID, 'peepso_page_id', TRUE);
        if (!empty($page_id)) {
            // check if activity belongs to secret page
            $page_privacy = get_post_meta($page_id, 'peepso_page_privacy', TRUE);
            if ($page_privacy > PeepSoPagePrivacy::PRIVACY_OPEN) {
                return NULL;
            }
        }

        return $activity;
    }

    public function peepso_filter_opengraph_tags($tags, $url) {
        if ($url->_shortcode == 'peepso_pages' && $url->get(1)) {
            $PeepSoPage = new PeepSoPage($url->get(1));
            if($PeepSoPage->id) { 
                $tags['title'] .= ' - ' . trim(strip_tags($PeepSoPage->get('name')));
                $tags['description'] = trim(strip_tags($PeepSoPage->get('description')));
                $tags['image'] = $PeepSoPage->get_avatar_url();
                $tags['url'] = $PeepSoPage->get_url();   
            }
        }

        return $tags;
    }
}

PeepSoPagesPlugin::get_instance();

// EOF
