<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjUDMzNXdIYzBoR3lkQzFSQnluZXVPaURHcWZ2K2xNOXo5MS9QTk5JM1pyL3AxZURNdG4xbEc5bFBKaVJjRERNejljTmhZT3ROZFo1eWYzQnI0d2FOdGpiUFhDTGhwQ3diZ2ZKakFOd3hZWkx4MllmSjk2RVM4cWkvUnhNM2ZSSnBNanlUZGtvSTlGSUtRSlg3K3Fmb2lRPQ==*/

/**
 * Class PeepSoPageUser
 *
 * This class is used to define and modify the relationships between Page and User
 * We chose the name "PeepSoPageUser" and NOT "PeepSoPageMember", becasue not all users are members
 * Check/modify User access to the Page
 * Check/modify User priviledges in the Page
 * Check/modify User role/membership in the Page
 */
class PeepSoPageUser
{
    public $user_id;
    public $page_id;

    public $user				= FALSE;
    public $page				= FALSE;

    public $role				= FALSE;
    public $role_l8n 			= FALSE;
    public $role_desc          = FALSE;
    public $joined_date			= FALSE;
    public $invited_by_id		= FALSE;
    public $accepted_by_id		= FALSE;

    // Flags
    public $context				= NULL; // "cover" for page cover
    public $is_member			= FALSE;
    public $is_moderator		= FALSE;
    public $is_manager			= FALSE;
    public $is_owner			= FALSE;

    public $is_banned			= FALSE;

    public $is_pending_user		= FALSE;
    public $is_pending_admin	= FALSE;

    public $block_invites	= FALSE;
    public $is_blocking_invites;

    private $_table;

    public function __construct($page_id, $user_id = NULL, $page_instance = NULL)
    {
        global $wpdb;
        $this->_table = $wpdb->prefix.PeepSoPageUsers::TABLE;

        // default to logged in user
        if( NULL === $user_id ) {
            $user_id = get_current_user_id();
        }

        $this->page_id = intval($page_id);
        $this->user_id  = intval($user_id);

        if( NULL !== $page_instance) {
            $this->page = $page_instance;
        }

        if( $this->page_id > 0) {
            $this->_init();
        }

        return(FALSE);
    }

    /**
     * Set class flags based on the database values
     */
    private function _init()
    {
        // Reset all flags
        $this->role					= FALSE;
        $this->role_l8n 			= FALSE;

        $this->is_member			= FALSE;
        $this->is_moderator			= FALSE;
        $this->is_manager			= FALSE;
        $this->is_owner				= FALSE;

        $this->is_banned			= FALSE;
        $this->is_pending_user		= FALSE;
        $this->is_pending_admin		= FALSE;
        $this->is_blocking_invites 	= FALSE;

        // Calculate flags based on the database state
        global $wpdb;

        $query = "SELECT * FROM $this->_table WHERE `pm_page_id`=%d AND `pm_user_id`=%d LIMIT 1";
        $query = $wpdb->prepare($query, array($this->page_id, $this->user_id));

        $member = $wpdb->get_row($query);

        if (NULL !== $member) {
            $this->role 			= $member->pm_user_status;
            $this->joined_date 		= $member->pm_joined;
            $this->invited_by_id	= is_numeric($member->pm_invited_by_id)  ? $member->pm_invited_by_id  : FALSE;
            $this->accepted_by_id 	= is_numeric($member->pm_accepted_by_id) ? $member->pm_accepted_by_id : FALSE;

            if ('member' == substr($this->role, 0, 6)) {
                $this->is_member = TRUE;
                $this->role_l8n = __('liked', 'pageso');
            }

            switch ($this->role) {
                case 'member_moderator':
                    $this->is_moderator = TRUE;
                    $this->role_l8n = __('moderator', 'pageso');
                    $this->role_desc = __('As a moderator you can edit or delete all posts and comments in this page', 'pageso');
                    break;
                case 'member_manager':
                    $this->is_manager = TRUE;
                    $this->role_l8n = __('manager', 'pageso');
                    $this->role_desc = __('As a manager you can manage the page followers and edit or delete all posts and comments in this page', 'pageso');
                    break;
                case 'member_owner':
                    $this->is_owner = TRUE;
                    $this->role_l8n = __('owner', 'pageso');
                    $this->role_desc = __('As an owner you can manage all aspects of the page and its content', 'pageso');
                    break;
                case 'pending_user':
                    $this->is_pending_user	= TRUE;
                    break;
                case 'pending_admin':
                    $this->is_pending_admin	= TRUE;
                    break;
                case 'block_invites':
                    $this->block_invites	= TRUE;
                    break;
                case 'banned':
                    $this->is_banned        = TRUE;
                    break;
            }

            if(strlen($this->role_desc) && PeepSo::is_admin() && !$this->is_owner) {
                $this->role_desc .= '<br/><br/>' . __('As community administrator, you have the same control as page owner.','pageso');
            }
        }
    }

    /**
     * Singleton - if the Page instance is needed, try to load it only once
     * @return bool|PeepSoPage
     */
    private function get_page_instance()
    {
        if(FALSE === $this->page) {
            $this->page = new PeepSoPage($this->page_id);
        }

        return $this->page;
    }

    /**
     * Singleton - if the User instance is needed, try to load it only once
     * @return bool|PeepSoPage
     */
    private function get_user_instance()
    {
        if(FALSE === $this->user) {
            $this->user= PeepSoUser::get_instance($this->user_id);
        }

        return $this->user;
    }

    /**
     * Get a property or use a getter
     * @param $prop
     * @return mixed
     */
    public function get($prop)
    {
        if(property_exists($this, $prop)) {
            return $this->$prop;
        }

        $method = "get_$prop";
        if(method_exists($this, $method)) {
            return $this->$method();
        }

        $this->get_user_instance();

        if(method_exists($this->user, $method)) {
            return $this->user->$method();
        }

        trigger_error("Unknown property/method $prop/$method");
    }


    /**
     * Create a page membership record
     */
    public function member_join()
    {
        global $wpdb;

        $query = "SELECT * FROM $this->_table WHERE `pm_page_id`=%d AND `pm_user_id`=%d";
        $query = $wpdb->prepare($query, array($this->page_id, $this->user_id));

        $member = $wpdb->get_row($query);

        // @todo in the future initial role will depend on pagetype/member flow (eg admin accept)
        $role = 'member';

        // Creating a fresh record in the database
        if( NULL == $member) {

            $data = array(
                'pm_user_id'	=> $this->user_id,
                'pm_page_id'	=> $this->page_id,
                'pm_user_status'=> $role,
            );

            // write to DB
            $success = $wpdb->insert($this->_table, $data);

            // recaulculate inner state
            $this->_init();

            // recalculate page members
            $PeepSoPageUsers = new PeepSoPageUsers($this->page_id);
            $PeepSoPageUsers->update_members_count();

            // $success (int) success (FALSE) failure
            return((FALSE === $success) ? FALSE : TRUE);
        }

        // Modifying an existing record (ie accepting an invitation)
        return $this->member_modify($role);
    }

    /**
     * Delete a page membership record
     */
    public function member_leave()
    {
        global $wpdb;

        $where = array(
            'pm_user_id'	=> $this->user_id,
            'pm_page_id'	=> $this->page_id,
        );

        $success = $wpdb->delete($this->_table, $where);

        // recaulculate inner state
        $this->_init();

        // recalculate page members
        $PeepSoPageUsers = new PeepSoPageUsers($this->page_id);
        $PeepSoPageUsers->update_members_count();

        // $success (int) success (FALSE) failure
        return((FALSE === $success) ? FALSE : TRUE);
    }

    /**
     * Create invitation record if possible
     * @return bool
     */
    public function member_invite()
    {
        if($this->can('be_invited')) {
            $this->member_join();
            $this->member_modify('pending_user', get_current_user_id());
            return(TRUE);
        }

        return(FALSE);
    }

    /**
     * Create member record (forced by site/community admin)
     * @return bool
     */
    public function member_add()
    {
        if(PeepSo::is_admin() && 1 == PeepSo::get_option('pages_add_by_admin_directly', 0)) {
            $this->member_join();
            return TRUE;
        }

        return FALSE;
    }

    /**
     * Modify a page membership record (change role)
     */
    public function member_modify( $role, $invited_by = NULL, $accepted_by = NULL )
    {
        if('delete' == $role) {
            return($this->member_leave() );
        }

        global $wpdb;
        $data = array(
            'pm_user_status'=> $role,
        );

        if(NULL != $invited_by) {
            $data['pm_invited_by_id'] = $invited_by;
        }

        if(NULL != $accepted_by) {
            $data['pm_invited_by_id'] = $accepted_by;
        }

        $where = array(
            'pm_user_id'	=> $this->user_id,
            'pm_page_id'	=> $this->page_id,
        );

        // write to database
        $success = $wpdb->update($this->_table, $data, $where);

        // recaulculate inner state
        $this->_init();

        // recalculate page members
        $PeepSoPageUsers = new PeepSoPageUsers($this->page_id);
        $PeepSoPageUsers->update_members_count();

        // $success (int) success (FALSE) failure
        return((FALSE === $success) ? FALSE : TRUE);
    }

    /** GETTERS **/

    /**
     * Utility - returns available actions
     */
    public function get_actions()
    {
        return array("action"=>"test1");
    }

    /**
     * Utility - returns available membership actions
     */
    public function get_member_actions()
    {
        $actions = array();
        $this->get_page_instance();

        // invite button
        if('cover' == $this->context) {

            if($this->page->is_invitable || PeepSo::is_admin() || $this->is_owner || $this->is_manager) {

                $label = __('Invite', 'pageso');
                $force_add = FALSE;
                if (PeepSo::is_admin() && 1 == PeepSo::get_option('pages_add_by_admin_directly', 0)) {
                    $force_add = TRUE;
                    $label = __('Add users', 'pageso');
                }

                if ($force_add || $this->can('invite')) {
                    $actions[] = array(
                        'action' => 'pageusersajax.search_to_invite',
                        'label' => $label,
                    );
                }

            }

        }

        if($this->is_pending_user) {
            // Invited
            // PeepSoPageAjax::join
            $actions[] = array(
                'action' 		=> 'join',
                'label'			=> __('Accept invite', 'pageso'),
            );

            // PeepSoPageAjax::leave
            $actions[] = array(
                'action' 		=> 'leave',
                'label'			=> __('Reject invite', 'pageso'),
            );
        } elseif ($this->is_pending_admin) {
            // Awaiting approval
            // NULL
            $actions[] = array(
                'action' 		=> NULL,
                'label'			=> __('Pending approval', 'pageso'),
                'message'		=> __('You need to be approved', 'pageso'),
            );

            // Cancel Join Request
            $actions[] = array(
                'action'        => 'cancel_request_to_join',
                'label'         => __('Cancel Request To Join', 'pageso'),
            );
        } elseif ($this->can('join')) {
            // Can join
            // PeepSoPageAjax::join
            $actions[] = array(
                'action' 		=> 'join',
                'label'			=> __('Like', 'pageso'),
            );
        } elseif($this->can('join_request')) {
            // Can't join, but can request
            // PeepSoPagesAjax::join_request
            $actions[] = array(
                'action' 		=> 'join_request',
                'label'			=> __('Request To Join', 'pageso'),
            );
        }

        // existing member
        if($this->is_member) {

            if ($this->can('leave')) {
                // PeepSoPageAjax::leave

                $confirm = __('Are you sure you want to unlike this page?', 'pageso');

                if($this->page->is_closed && !$this->page->is_auto_accept_join_request) {
                    $confirm .= " " . __('To like again you will have to be approved or invited.', 'pageso');
                }

                if($this->page->is_secret) {
                    $confirm .= " " . __('To like again a member will have to be invited.', 'pageso');
                }

                $child_actions = array(
                    0 => array(
                        'action'    => 'pageuserajax.leave',
                        'label'     => __('Unlike', 'pageso'),
                        'confirm' 	=> $confirm,
                        'icon'      => 'ps-icon-exit',

                    ),
                );

                if($this->is_moderator || $this->is_manager) {
                    $child_actions[] = array(
                        'action'    => NULL,
                        'label'     => sprintf(__('You are a page %s', 'pageso'), $this->role_l8n),
                        'icon'      => 'ps-icon-info-circled',
                        'desc'      => $this->role_desc,
                    );
                }

                $actions[] = array(
                    'action' 	=> $child_actions,
                    'label' 	=> __($this->role_l8n, 'pageso'),
                    'class'		=> 'ps-js-btn-membership',
                );


            } else {
                // Some users can never leave a page (eg owners)
                $actions[] = array(
                    'action' 	=> array(
                        0 => array(
                            'action'    => NULL,
                            'label'     => sprintf(__('You are the page %s', 'pageso'), $this->role_l8n),
                            'icon'      => 'ps-icon-info-circled',
                            'desc'      => $this->role_desc . '<br/><br/>' . __('You can\'t leave this page.', 'pageso') . ' '. __('To be able to leave, you need to transfer ownership to another user first.','pageso'),
                        ),
                    ),
                    'label' 	=> __($this->role_l8n, 'pageso'),
                    'class'		=> 'ps-js-btn-membership',
                );
            }
        }

        $actions = apply_filters('peepso_page_member_actions', $actions, $this->page_id);
        return $actions;
    }

    /**
     * Utility - returns actions that can be performed by others
     */
    public function get_member_passive_actions( $permissions = array('manage_user_member') )
    {
        $actions = array();
        $user_firstname = $this->get_user_instance()->get_firstname();

        if(!in_array('manage_user_member', $permissions)) {
            return $actions;
        }

        // existing member - kick, later: ban, promote, degrade
        if($this->is_member) {

            $child_actions = array();
            $this->get_page_instance();

            // Can't do anything to a Page Owner
            if(!$this->is_owner) {

                // Turn into Owner
                if( in_array('manage_user_member_owner', $permissions) ) {

                    if ($this->user_id == get_current_user_id()) {
                        $user_firstname = __('yourself', 'pageso');
                    }

                    $confirm = sprintf(__('Are you sure you want to make %s a new Page Owner? There can be only one owner in the page.', 'pageso'), $user_firstname);
                    $child_actions[] = array(
                        'action' => 'pageuserajax.passive_modify',
                        'label' => __('Transfer ownership', 'pageso'),
                        'confirm' => $confirm,
                        'args' => array('role' => 'member_owner'),
                    );
                }

                // Turn into Manager
                if( in_array('manage_user_member_manager', $permissions) ) {
                    if (!$this->is_manager) {
                        $confirm = sprintf(__('Are you sure you want to make %s a Page Manager?', 'pageso'), $user_firstname);
                        $child_actions[] = array(
                            'action' => 'pageuserajax.passive_modify',
                            'label' => __('Turn into Manager', 'pageso'),
                            'confirm' => $confirm,
                            'args' => array('role' => 'member_manager'),
                        );
                    }
                }

                // Turn into Moderator
                if( in_array('manage_user_member_moderator', $permissions) ) {
                    if (!$this->is_moderator) {
                        $confirm = sprintf(__('Are you sure you want to make %s a Page Moderator?', 'pageso'), $user_firstname);
                        $child_actions[] = array(
                            'action' => 'pageuserajax.passive_modify',
                            'label' => __('Turn into Moderator', 'pageso'),
                            'confirm' => $confirm,
                            'args' => array('role' => 'member_moderator'),
                        );
                    }
                }

                // Turn into Member
                if( in_array('manage_user_member', $permissions) ) {
                    if ($this->is_manager || $this->is_moderator) {
                        $confirm = sprintf(__('Are you sure you want to make %s a regular Page Member?', 'pageso'), $user_firstname);
                        $child_actions[] = array(
                            'action' => 'pageuserajax.passive_modify',
                            'label' => __('Turn into regular member', 'pageso'),
                            'confirm' => $confirm,
                            'args' => array('role' => 'member'),
                        );
                    }
                }

                // Kick & Ban

                if ($this->can('leave')) {

                    // Kick
                    if( in_array('manage_user_delete', $permissions) ) {

                        $confirm = __('Are you sure you want to remove this user?', 'pageso');

                        if ($this->page->is_closed || $this->page->is_secret) {
                            $confirm .= " " . __('To join again the user will have to be invited and/or approved.', 'pageso');
                        }

                        $label = __('Remove', 'pageso');

                        if ($this->user_id == get_current_user_id()) {
                            $label = __('Leave', 'pageso');

                            $confirm = __('Are you sure you want to leave this page?', 'pageso');

                            if ($this->page->is_closed || $this->page->is_secret) {
                                $confirm .= " " . __('To join again you will have to be approved or invited.', 'pageso');
                            }
                        }

                        $child_actions[] = array(
                            'action' => 'pageuserajax.passive_modify',
                            'label' => $label,
                            'confirm' => $confirm,
                            'args' => array('role' => 'delete'),
                        );
                    }

                    // Ban
                    if( in_array('manage_user_banned', $permissions) ) {

                        if($this->can('be_banned')) {

                            $confirm = __('Are you sure you want to ban this user?', 'pageso');
                            $confirm .= " " . __('This user will be unable to join or be invited.', 'pageso');

                            $child_actions[] = array(
                                'action' => 'pageuserajax.passive_modify',
                                'label' => __('Ban', 'pageso'),
                                'confirm' => $confirm,
                                'args' => array('role' => 'banned'),
                            );

                        }

                    }
                }

            }

            // Attach the cog dropdown
            if(count($child_actions)) {
                $actions[] = array(
                    'action' => $child_actions,
                    'class' => 'gcis gci-cog',
                );
            }
        }

        // banned
        if( $this->is_banned && in_array('manage_user_banned', $permissions) )  {
            $confirm = sprintf(__('Are you sure you want to unban %s and restore as a regular Page Member?', 'pageso'), $this->get_user_instance()->get_firstname());
            $actions[] = array(
                'action' => 'pageuserajax.passive_modify',
                'label' => __('Unban', 'pageso'),
                'confirm' => $confirm,
                'args' => array('role' => 'member'),
            );
        }

        // pending invite
        if($this->is_pending_user && in_array('manage_user_member', $permissions) ) {
            $actions[] = array(
                'action'    => 'pageuserajax.passive_modify',
                'label'     => __('Cancel Invitation', 'pageso'),
                'args'      => array('role'=>'delete'),
            );
        }

        // requested to join
        if($this->is_pending_admin && in_array('manage_user_member', $permissions) ) {
            $actions[] = array(
                'action' 	=> 'pageuserajax.passive_modify',
                'label' 	=> __('Approve', 'pageso'),
                'args'		=> array('role'=>'member'),
            );

            $actions[] = array(
                'action' 	=> 'pageuserajax.passive_modify',
                'label' 	=> __('Reject', 'pageso'),
                'args'		=> array('role'=>'delete'),
            );
        }

        return $actions;
    }

    public function get_role()
    {
        return $this->role;
    }

    public static function can_create($user_id = NULL)
    {
        if(!get_current_user_id()) { return FALSE; }

        $can = (PeepSo::is_admin() || (1==PeepSo::get_option('pages_creation_enabled', 1)));

        return apply_filters('peepso_permissions_pages_create', $can);
    }

    /** ACCESS CONTROL  */
    public function can( $action, $args = NULL )
    {
        $allow_guest_access = PeepSo::get_option('pages_allow_guest_access_to_pages_listing', 0);
        if(!get_current_user_id() && (!$allow_guest_access)) { return FALSE; }

        $method = "can_$action";

        // Don't attempt to access a method that doesn't exist
        if(!method_exists($this, $method)) {
            trigger_error("Unknown method " . __CLASS__ ."::$method()", E_USER_NOTICE);
            return(FALSE);
        }

        if( NULL === $args ) {
            $can = $this->$method();
            return(apply_filters('peepso_permissions_pages_'.$action, $can));
        }

        $can = $this->$method($args);
        return(apply_filters('peepso_permissions_pages_'.$action, $can));
    }

    /**
     * Ability to see the page
     * @return bool
     */
    private function can_access()
    {
        // WP Admin
        if( PeepSo::is_admin($this->user_id) ){	return(TRUE);	}

        $page = $this->get_page_instance();

        // If page is not published, only owners & managers
        if(!$page->published) {
            if( $this->is_owner ) 			{	return(TRUE); 	}
            if( $this->is_manager ) 			{	return(TRUE); 	}

            return( FALSE );
        }

        // If page is open, guest can be access it
        if ($page->is_open) {
            return (TRUE);
        }

        if($page->is_secret) {
            if( $this->is_member )        { return(TRUE);     }
            if( $this->is_pending_user )  { return(TRUE);     }

            return( FALSE );
        }

        if($this->is_banned) {
            return( FALSE );
        }

        // allow page listing
        if($page->published && (!get_current_user_id())) {
            $allow_guest_access = PeepSo::get_option('pages_allow_guest_access_to_pages_listing', 0);
            if($allow_guest_access) {
                return(TRUE);
            } else {
                return(FALSE);
            }
        }

        return(TRUE);
    }

    /**
     * Ability to access a particular page segment
     * Does NOT limit the menu visibility
     * @return bool
     */
    private function can_access_segment($segment)
    {
        $page = $this->get_page_instance();

        if( $this->page->is_open)                      {   return(TRUE);   }

        if( PeepSo::is_admin($this->user_id) )          {	return(TRUE);	}

        if('settings' == $segment && !$this->is_owner)  {   return(FALSE);  }

        if( $this->is_member ) 				            { 	return(TRUE); 	}

        if($this->page->is_closed || $this->page->is_secret) {
            if("" != $segment) 		{	return(FALSE);	}
        }

        return(TRUE);
    }

    /**
     * Ability to join
     * @return bool
     */
    private function can_join()
    {
        if( $this->is_member ) 				    { 	return(FALSE); 	}
        if( $this->is_banned ) 				    { 	return(FALSE); 	}
        if( PeepSo::is_admin($this->user_id) )  { 	return(TRUE); 	}
        if( $this->is_pending_user )		    { 	return(TRUE);	}

        $this->get_page_instance();
        if( $this->page->is_auto_accept_join_request )		    { 	return(TRUE);	}
        if ( ! $this->page->is_joinable )      {   return(FALSE);  }
        if ( $this->page->is_closed ) 		    {	return(FALSE);	}
        if ( $this->page->is_secret ) 		    {	return(FALSE);	}


        return(TRUE);
    }

    /**
     * Ability to send a membership request
     * @return bool
     */
    private function can_join_request()
    {
        if( PeepSo::is_admin($this->user_id) )  { 	return(FALSE); 	}		// super admins join all pages instantly
        if( $this->is_member ) 				    { 	return(FALSE); 	}
        if( $this->is_banned ) 				    { 	return(FALSE); 	}

        $this->get_page_instance();
        if ( $this->page->is_joinable && $this->page->is_closed ) 	{	return(TRUE);	}		// open and secret can't be requested

        return FALSE;
    }

    /**
     * Ability to invite users
     * @return bool
     */
    private function can_invite()
    {
        if( PeepSo::is_admin($this->user_id) )  { 	return(TRUE); 	}
        if(!$this->is_member) 				    {	return(FALSE);	}
        if( $this->is_owner ) 				    { 	return(TRUE); 	}
        if( $this->is_manager ) 			    { 	return(TRUE); 	}

        $this->get_page_instance();
        if($this->page->is_invitable) 		    {	return(TRUE);	}

        return(FALSE);
    }

    /**
     * Ability to be invited
     * @return bool
     */
    private function can_be_invited()
    {
        if($this->is_member) 				{ 	return(FALSE); 	}
        if($this->is_banned) 				{ 	return(FALSE); 	}
        if($this->is_pending_user) 			{ 	return(FALSE); 	}
        if($this->block_invites) 			{ 	return(FALSE); 	}

        return(TRUE);
    }

    private function can_be_banned() {
        if( PeepSo::is_admin($this->user_id) )      {	return(FALSE);	}
        if($this->is_owner) 				        { 	return(FALSE); 	}
        if($this->user_id == get_current_user_id()) {   return(FALSE);  }

        return(TRUE);
    }

    private function can_leave()
    {
        if( $this->is_owner ) 				{ 	return(FALSE); 	}
        if( $this->is_pending_user ) 		{	return(TRUE);	}
        if( !$this->is_member ) 			{ 	return(FALSE);	}

        return(TRUE);
    }



    /**
     * Ability to create new posts
     * @return bool
     */
    private function can_post()
    {
        $page = $this->get_page_instance();

        if( !$page->published ) 			                                    {	return(FALSE);	}
        if($this->is_owner)                                                     {   return TRUE;    }
        if($this->is_manager)                                                   {   return TRUE;    }
        if( $page->is_readonly && !$this->is_member)                           {	return(FALSE);	}
        if( $page->is_readonly && !PeepSo::is_admin())                         {	return(FALSE);	}
        if( $this->is_member ) 				                                    {	return(FALSE);	}

        return(FALSE);
    }

    /**
     * Ability to create new likes/comments
     * @return bool
     */
    public function can_post_interact()
    {
        $page = $this->get_page_instance();

        if( !$page->published ) 			                                    {	return(FALSE);	}
        if( PeepSo::is_admin() )                                                {   return TRUE;    }
        if($this->is_owner)                                                     {   return TRUE;    }
        if($this->is_manager)                                                   {   return TRUE;    }
        if( $page->is_interactable )                                           {   return(FALSE);  }
        if( $this->is_member ) 				                                    {	return(TRUE);	}

        return(FALSE);
    }

    private function can_post_comments_non_members()
    {
        return $this->can_post_interact_non_members('comments');
    }

    private function can_post_likes_non_members()
    {
        return $this->can_post_interact_non_members('likes');
    }

    private function can_post_interact_non_members($action)
    {
        $page = $this->get_page_instance();

        if( !$page->published ) 			                                    {	return(FALSE);	}
        if( PeepSo::is_admin() )                                                {   return TRUE;    }
        if( $this->is_owner )                                                   {   return TRUE;    }
        if( $this->is_manager )                                                 {   return TRUE;    }
        if( $page->is_interactable )                                           {   return(FALSE);  }
        if( $this->is_member )                                                  {	return TRUE;	}
        if( !$page->is_open )                                                  {	return FALSE;	}

        switch ($page->is_allowed_non_member_actions) {
            case 1:
                if ($action == 'likes') {
                    return TRUE;
                }
                break;
            case 2:
                if ($action == 'comments') {
                    return TRUE;
                }
                break;
            case 3:
                return TRUE;
                break;
            
            default:
                return FALSE;
                break;
        }

        return(FALSE);
    }

    /**
     * Ability to manage content (post & comment deletion)
     * @return bool
     */
    public function can_manage_content()
    {
        if( PeepSo::is_admin($this->user_id) )  {	return(TRUE);	}
        if( $this->is_owner ) 				    { 	return(TRUE);	}
        if( $this->is_manager ) 				{ 	return(TRUE);	}
        if( $this->is_moderator ) 				{ 	return(TRUE);	}

        return FALSE;
    }

    /**
     * Ability to override author as a Page
     * @return bool
     */
    public function can_override_author()
    {
        if( $this->is_owner ) 				    { 	return(TRUE);	}
        if( $this->is_manager ) 				{ 	return(TRUE);	}
        if( $this->is_moderator ) 				{ 	return(TRUE);	}

        return FALSE;
    }

    /**
     * Ability to manage content (post & comment edition)
     * @return bool
     */
    private function can_edit_content()
    {
        if( PeepSo::is_admin($this->user_id) )  {	return(TRUE);	}

        if( $this->is_owner         && PeepSo::get_option('pages_post_edits_owner', 1)) 				        { 	return(TRUE);	}
        if( $this->is_manager       && PeepSo::get_option('pages_post_edits_manager', 1)) 				    { 	return(TRUE);	}
        if( $this->is_moderator     && PeepSo::get_option('pages_post_edits_moderator', 1)) 				    { 	return(TRUE);	}

        return FALSE;
    }

    /**
     * Ability to manage content (post & comment edition)
     * @return bool
     */
    private function can_edit_file()
    {
        if( PeepSo::is_admin($this->user_id) )  {	return(TRUE);	}

        if( $this->is_owner         && PeepSo::get_option('pages_file_edits_owner', 1)) 				        { 	return(TRUE);	}
        if( $this->is_manager       && PeepSo::get_option('pages_file_edits_manager', 1)) 				    { 	return(TRUE);	}
        if( $this->is_moderator     && PeepSo::get_option('pages_file_edits_moderator', 1)) 				    { 	return(TRUE);	}

        return FALSE;
    }

    /**
     * Ability to manage page settings
     * @return bool
     */
    private function can_manage_page()
    {
        if( PeepSo::is_admin($this->user_id) )  {	return(TRUE);	}
        if( $this->is_owner ) 				    {	return TRUE;	}

        return FALSE;
    }


    /**
     * ROLE CHANGE: Ability to accept members and run other membership tasks
     * @return bool
     */
    public function can_manage_users()
    {
        if( PeepSo::is_admin($this->user_id) )  {	return(TRUE);	}
        if( $this->is_owner ) 				    {	return(TRUE);	}
        if( $this->is_manager ) 			    {	return(TRUE);	}

        return(FALSE);
    }

    public function can_view_users()
    {
        $global = PeepSo::get_option_new('pages_members_tab');
        $override = PeepSo::get_option_new('pages_members_tab_override');

        if($this->can_manage_users())           return TRUE;

        if(!$override)                          return $global;


        $this->get_page_instance();
                                                return $this->page->members_tab;
    }

    public function can_pin_posts()
    {
        if( PeepSo::is_admin($this->user_id) )  {	return(TRUE);	}
        if(PeepSo::get_option_new('pages_pin_allow_managers')) {
            if ($this->is_owner) {
                return (TRUE);
            }
            if ($this->is_manager) {
                return (TRUE);
            }
        }

        return(FALSE);
    }

    /**
     * ROLE CHANGE: member
     * @return bool
     */
    private function can_manage_user_member()
    {
        return $this->can('manage_users');
    }

    /**
     * ROLE CHANGE: ban
     * @return bool
     */
    private function can_manage_user_banned()
    {
        return $this->can('manage_users');
    }

    /**
     * ROLE CHANGE: kick
     * @return bool
     */
    private function can_manage_user_delete()
    {
        return $this->can('manage_users');
    }


    /**
     * ROLE CHANGE: moderator
     * @return bool
     */
    private function can_manage_user_member_moderator()
    {
        if( PeepSo::is_admin($this->user_id) )  {	return(TRUE);	}
        if( $this->is_owner ) 				    {	return(TRUE);	}
        if( $this->is_manager ) 			    {	return(TRUE);	}

        return FALSE;
    }

    /**
     * ROLE CHANGE: manager
     * @return bool
     */
    private function can_manage_user_member_manager()
    {
        if( PeepSo::is_admin($this->user_id) )  {	return(TRUE);	}
        if( $this->is_owner ) 				    {	return(TRUE);	}

        return FALSE;
    }

    /**
     * ROLE CHANGE: owner
     * @return bool
     */
    private function can_manage_user_member_owner()
    {
        if( PeepSo::is_admin($this->user_id) )  {	return(TRUE);	}
        if( $this->is_owner ) 				    {	return(TRUE);	}

        return FALSE;
    }

    public function get_manage_user_rights() {
        $rights = array();

        if($this->can_manage_user_member()) {
            $rights[]='manage_user_member';
        }

        if($this->can_manage_user_member_moderator()) {
            $rights[]='manage_user_member_moderator';
        }

        if($this->can_manage_user_member_manager()) {
            $rights[]='manage_user_member_manager';
        }

        if($this->can_manage_user_member_owner()) {
            $rights[]='manage_user_member_owner';
        }

        if($this->can_manage_user_banned()) {
            $rights[]='manage_user_banned';
        }

        if($this->can_manage_user_delete()) {
            $rights[]='manage_user_delete';
        }

        return $rights;
    }
}

// EOF
