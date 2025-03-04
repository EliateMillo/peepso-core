<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjUDMzNXdIYzBoR3lkQzFSQnluZXVPaThxZVprUWltVWMyWDJ5bklpY3gxMEc5Q1hxcGd5ZUFsMTBWSSszODl6bHEyZ0M0bG9OcHlrV2dTL3ZVcjBlM29Td0ZvZWw4TmxsbkpOa0pqT3hZdlo3MzJ5K3FCdkpENzc1Z2xYVGhaRzA3K0ZoOUFNd1haUlNrVkJGZ01ZTFlVPQ==*/
require_once(PeepSo::get_plugin_dir() . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'install.php');
/*
 * Performs installation process
 * @package PeepSoVideos
 * @author PeepSo
 */
class PeepSoPagesInstall extends PeepSoInstall
{
	// these items are stored under the mail 'peepso_config' option
	protected $default_config = array(
			'pages_creation_enabled' => 1,
			'pages_creation_enabled_description' => 1,
			'pages_listing_show_page_owner' => 1,
			'pages_listing_show_page_creation_date' => 1,
			'pages_categories_enabled' => 0,
			'pages_categories_multiple_max' => 1,
			'pages_categories_hide_empty' => 0,
            'pages_pin_allow_managers' => 1,
            'pages_members_tab'        => 1,
		);

	/*
	 * called on plugin activation; performs all installation tasks
	 */
	public function plugin_activation( $is_core = FALSE )
	{
		parent::plugin_activation($is_core);
		return (TRUE);
	}

	/*
	 * return default page names information
	 */
	protected function get_page_data()
	{
		// default page names/locations
		$aRet = array(
			'pages' => array(
				'title' => __('Pages', 'msgso'),
				'slug' => 'pages',
				'content' => '[peepso_pages]',
			),
		);

		return ($aRet);
	}

	public static function get_table_data()
	{
		$aRet = array(
		    'page_followers'=>"
		      CREATE TABLE page_followers (
                  pf_id int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                  pf_page_id bigint(20) UNSIGNED NOT NULL,
                  pf_user_id bigint(20) UNSIGNED  NOT NULL,
                  pf_follow smallint(1) UNSIGNED  NOT NULL DEFAULT 1,
                  pf_notify smallint(1) UNSIGNED  NOT NULL DEFAULT 1,
                  pf_email smallint(1) UNSIGNED  NOT NULL DEFAULT 1,
                  PRIMARY KEY (pf_id),
				  INDEX pf_page (pf_page_id),
				  INDEX pf_user (pf_user_id),
				  INDEX pf_follow (pf_follow),
				  INDEX pf_notify (pf_notify),
				  INDEX pf_email (pf_email)
            ) ENGINE=InnoDB",

			'page_members' => "
				CREATE TABLE page_members (
					pm_id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
					pm_user_id BIGINT(20) UNSIGNED NOT NULL,
					pm_page_id BIGINT(20) UNSIGNED NOT NULL,
					pm_user_status enum('member_readonly','member','member_moderator','member_manager','member_owner','pending_user','pending_admin','banned','block_invites') NOT NULL,
					pm_joined TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
					pm_invited_by_id BIGINT(20) UNSIGNED DEFAULT NULL,
					pm_accepted_by_id BIGINT(20) UNSIGNED DEFAULT NULL,
					PRIMARY KEY (pm_id),
					INDEX pm_user_id (pm_user_id),
					INDEX pm_page_id (pm_page_id),
					INDEX pm_user_status (pm_user_status)
				) ENGINE=InnoDB",

			'page_categories' => "
				CREATE TABLE page_categories (
					pc_id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
				  	pm_cat_id BIGINT(20) UNSIGNED NOT NULL,
					pm_page_id BIGINT(20) UNSIGNED NOT NULL,
					PRIMARY KEY (pc_id),
					INDEX pc_cat_id (pm_cat_id),
					INDEX pc_page_id (pm_page_id)
				) ENGINE=InnoDB",
		);


		return $aRet;
	}

    public function get_email_contents()
    {
        $emails = array(
            'email_page_new_post' => "Hi {firstname},

{fromfirstname} just wrote a post in {pagename}. You can see it here: {permalink}

Thank you.",

            'email_page_created' => "Hi {firstname},

{fromfirstname} just created a new page: {pagename}


You can see it here: {permalink}

Thank you.",
			'email_user_comment_page_avatar' => "Hi {firstname},

{fromfirstname} had something to say about your page avatar!

You can see the avatar here:

{permalink}

Thank you.",
			'email_user_comment_page_cover' => "Hi {firstname},

{fromfirstname} had something to say about your page cover!

You can see the cover here:

{permalink}

Thank you.",

        );

        return $emails;
    }
}