<div class="ps-chat__info ps-js-message ps-js-message-<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjUDMzNXdIYzBoR3lkQzFSQnluZXVPaDNITGJSL3VKOUNlUDVWM2JRb0dxM3lRSGl3ZCtUSDhJWi82SytYWnYyeXFnNDMrMVdmdjd2dzRaamN0bzJYWFpxRGhDamFiZVpiSDczQlJJRnhZVVpBVGZ5TnRDeVVxdGQxaFhKclZzRjB5d1ZEaFRraytvdE9nWEJtT2k5ZGFtbmFiL2hndGtZSU0waThjTzVvY3Vn*/ echo $ID ?>" data-id="<?php echo $ID ?>">
    <div class="ps-alert ps-alert--neutral ps-alert--cp">
      <i class="gcis gci-user-alt-slash"></i>
	    <em><?php
	    	$user = PeepSoUser::get_instance($post_author);
			if( 'left' == $post_content) {
				printf(__('%s has left the conversation', 'msgso'), $user->get_fullname());
			}

			if( 'new_group' == $post_content) {
				printf(__('%s created a new group conversation', 'msgso'), $user->get_fullname());
			}

	    ?></em>
    </div>
</div>
