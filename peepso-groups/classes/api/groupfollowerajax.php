<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjUDMzNXdIYzBoR3lkQzFSQnluZXVPanBVemdicXgxbFphYlpGMFR1ei9ObERoZU1SdDBoTkhUVndKb2duY0tCRnVWUTVKcndkRjE0dnp0TUhFcjViYitPUUhUWnd3ck4zMWpmUmRQNkpmRFQrSjI3MFhTUDErZUtCdi8xWFYxQzBlYzd4V25KYkNUcmhJYVNYcDBwekV1RW1pdG5WYWQ5NmJDZ05PQU9ERGxG*/

class PeepSoGroupFollowerAjax extends PeepSoGroupAjaxAbstract
{

    protected function __construct()
    {
        parent::__construct();

        if($this->_group_id > 0) {
            $this->_model= new PeepSoGroupFollower($this->_group_id, $this->_user_id);
        }
    }

    public function init($group_id)
    {
        $this->_group_id = $group_id;
        $this->_model = new PeepSoGroupFollower($this->_group_id, $this->_user_id);
    }

    public function follower_actions(PeepSoAjaxResponse $resp = NULL) {
        $response = $this->_model->get('follower_actions');

        if(NULL == $resp) {
            return($response);
        }

        $resp->set('follower_actions', $response);
    }

    public function set(PeepSoAjaxResponse $resp) {

        // it's basically impossible to set invalid prop/val, so no validation required

        // SQL Safe
        $prop = $this->_input->value('prop', '', false);
        $value = $this->_input->int('value');

        $success = $this->_model->set( $prop, $value );

        // Force disable emails if on-site is disabled
        if($prop == 'notify' && 0 == $value) {
            $this->_model->set( 'email', 0 );
        }

        // Force enable on-site if email is enabled
        if($prop == 'email' && 1 == $value) {
            $this->_model->set( 'notify', 1 );
        }


        $resp->success(TRUE);

        $resp->set('follower_actions', $this->_model->get('follower_actions'));
    }
}