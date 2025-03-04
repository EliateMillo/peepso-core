<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjUDMzNXdIYzBoR3lkQzFSQnluZXVPaHUrMGZhV29QdGtaVWkvS1E3TS9BYzNhMEd2V2NJM3ByOFdDUG13OU1pbXRhN1ZBVDMzNTdjcUovWWs3QzFBczBncGFMaHR2RmlPWmEyOEZ1Qk55eEdUbDlTT2plQVNqL05OeXFYS2w5T0g4U2ptR3hZZzgzbjZBdjR2ekpRcmlpN09yYkMvUUZHWkx3ckhDd1VxWmt0*/

class PeepSoConfigSectionMessages extends PeepSoConfigSectionAbstract
{
    // Builds the groups array
    public function register_config_groups()
    {
        $this->context='left';
        $this->group_messages();

        $this->context='right';
        $this->group_chat();
        $this->group_chat_pages();
    }


    /**
     * General Settings Box
     */
    private function group_messages()
    {
        $section = 'messages_';

        // # Enable "read" notifications
        $this->set_field(
            'messages_read_notification',
            __('Enable "read" notifications', 'msgso'),
            'yesno_switch'
        );

        // # Enable compose in new page
        $this->set_field(
            'messages_compose_in_new_page',
            __('Enable "compose" in new page', 'msgso'),
            'yesno_switch'
        );

        if(class_exists('PeepSoFriends')) {
            $this->args('default', TRUE);
            $this->set_field(
                'messages_friends_only',
                __('Allow new messages only from friends', 'msgso'),
                'yesno_switch'
            );
        }

        // # Message length limit
        $this->args('int', TRUE);
        $this->args('validation', array('required','numeric'));

	    $this->set_field(
		    'messages_limit',
		    __('Character limit per message', 'peepso-hello-world'),
		    'text'
	    );

		$options = [];
		for($i = 1; $i <= 24; $i++) {
			$options[$i] = $i ." ". _n('hour', 'hours', $i, 'msgso');
		}
		$this->args('options', $options);
		$this->args('descript',"An email will be sent on first message. This setting controls how often to send emails on subsequent messages");
	    $this->set_field(
		    'messages_email_delay',
		    __('Email notification intensity', 'msgso'),
		    'select'
	    );

        $this->set_group(
            'peepso_messages',
            __('Messages', 'msgso')
        );
    }

    /**
     * Custom Greeting Box
     */
    private function group_chat()
    {
        // # Use Custom Greeting
        $this->args('default', 1);

        $this->set_field(
            'messages_chat_enable',
            __('Enable Chat', 'msgso'),
            'yesno_switch'
        );

        $this->args('descript',__('Send an extra ajax call every 30 seconds to make sure that messages and chat is synchronized between multiple open browser windows or devices.', 'msgso'));
        $this->set_field(
            'messages_get_chats_longpoll',
            __('Forced Refresh', 'msgso'),
            'yesno_switch'
        );

        $this->set_group(
            'peepso_messages_chat',
            __('Chat', 'msgso')
        );
    }

    private function group_chat_pages()
    {

        if(PeepSo3_Helper_Addons::license_is_free_bundle( FALSE)) {
            $this->set_field(
                'chat_pages_disabled',
                PeepSo3_Helper_Addons::get_pfb_disabled_text(),
                'message'
            );
        } else {
            $options = array(
                '0' => __('Disable on selected pages', 'msgso'),
                '1' => __('Show only on selected pages', 'msgso'),
            );

            $this->args('options', $options);
            $this->args('descript', __('Select mode in which chat should or should not appear.', 'msgso'));
            $this->set_field(
                'messages_chat_restriction_mode',
                __('Page-restriction mode', 'msgso'),
                'select'
            );

            $this->args('descript', __('List of pages in which chat windows <strong>should not</strong> appear, one page per line.<br />Examples:<br /><strong>/</strong> (Homepage)<br /><strong>/profile</strong> (Profile page)', 'msgso'));
            $this->set_field(
                'messages_chat_disable_on_pages',
                __('Disable on pages', 'msgso'),
                'textarea'
            );

            $this->args('descript', __('List of pages in which chat windows <strong>should</strong> appear, one page per line.<br />Examples:<br /><strong>/</strong> (Homepage)<br /><strong>/profile</strong> (Profile page)', 'msgso'));
            $this->set_field(
                'messages_chat_enable_on_pages',
                __('Enable on pages', 'msgso'),
                'textarea'
            );
        }

        $this->set_group(
            'peepso_messages_chat_pages',
            __('Chat & Pages', 'msgso')
        );
    }


}
