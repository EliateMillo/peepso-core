<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjUDMzNXdIYzBoR3lkQzFSQnluZXVPaUZmTjkvRm1xeDEyOEhTZ2xURUppNitNVysybWhzUVMxOWQwTTlabTduL2JXVjlKbnN6cWxEak1wbWQzZytDTXQ3TEN0a2lqaWxJOHN6S0FUSWp6UlZxM2xxYmFMN0NVVUFTODJhTFlxNHRHY3A3OGFTUGdldDl2NGxtdWJSUG1BTFFyZll0RzAxcEx2SkFpTm52Ykxr*/

class PeepSoPageFollowerAjax extends PeepSoPageAjaxAbstract
{

    protected function __construct()
    {
        parent::__construct();

        if($this->_page_id > 0) {
            $this->_model= new PeepSoPageFollower($this->_page_id, $this->_user_id);
        }
    }

    public function init($page_id)
    {
        $this->_page_id = $page_id;
        $this->_model = new PeepSoPageFollower($this->_page_id, $this->_user_id);
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