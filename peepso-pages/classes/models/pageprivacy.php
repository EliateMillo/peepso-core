<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjUDMzNXdIYzBoR3lkQzFSQnluZXVPakkzd3hpNFpvdWFScXY5U3poNVFFZmpFNFBmU3Y2M3V0SHgyVXgwMEdDQU5uNlBYV2F4UjExV3RLazN0SEhNN0RDWFhsL1JTdEwxVkxZUDRBSzhycW9HN2ZleS9tWU51dWNqNmZQVTJ3ZzF2bHNvUTdKT2gxbFJmd0NENkY5L2hzMDZQQ09uQy8yUEJhYzQwQUJtK0dT*/

class PeepSoPagePrivacy
{
	public $settings = array();

	const PRIVACY_OPEN 		= 0;
	const PRIVACY_CLOSED 	= 1;
	const PRIVACY_SECRET 	= 2;

	public static function _($privacy = NULL)
	{
		$settings = array(

			self::PRIVACY_OPEN => array(
				'id' 	=> self::PRIVACY_OPEN,
				'icon'	=> 'gcis gci-globe-americas',
                'name'	=> __('Open', 'pageso'),
                'notif'	=> __('open', 'pageso'),
				'desc'	=> __('Non-members can see the page content, but they can\'t post.','pageso'),
			),

            self::PRIVACY_CLOSED => array(
                'id'	=> self::PRIVACY_CLOSED,
                'icon'	=> 'gcis gci-lock',
                'name'	=> __('Private', 'pageso'),
                'notif'	=> __('private', 'pageso'),
                'desc'	=> __('Users need to be invited or request the page membership.', 'pageso') . PHP_EOL . htmlspecialchars(__('Non-members can only see the page page.','pageso')),
            ),
            self::PRIVACY_SECRET=> array(
                'id'	=> self::PRIVACY_SECRET,
                'icon'	=> 'gcis gci-shield-alt',
                'name'	=> __('Secret', 'pageso'),
                'notif' => __('secret', 'pageso'),
                'desc'	=> __('Users need to be invited.','pageso') . PHP_EOL .  __('Non-members can\'t see the page at all.', 'pageso'),
            ),
		);

		// Return a single privacy setting if requested
		if(NULL !== $privacy) {
			return $settings[$privacy];
		}


		// Otherwise return everything
		return apply_filters('peepso_filter_pages_privacy_options', $settings);
	}

    public static function _default() {
        $options = self::_();
        $options = array_reverse($options);
        return array_pop($options);
    }

    /**
     * Displays the privacy options in an unordered list.
     * @param string $callback Javascript callback
     */
    public static function render_dropdown($callback = '')
    {
        ob_start();

        echo '<div class="ps-dropdown__menu ps-js-dropdown-menu">';

        $options = self::_();

        foreach ($options as $key => $option) {
            printf('<a href="#" class="ps-dropdown__page" data-option-value="%d" onclick="%s; return false;">%s</a>',
                $key, $callback, '<div class="ps-dropdown__page-title"><i class="' . $option['icon'] . '"></i><span>' . $option['name'] . '</span></div><div class="ps-dropdown__page-desc">' . nl2br($option['desc']) .'</div>'
            );
        }
        echo '</div>';

        return ob_get_clean();
    }
}
