<div class="ps-conversation-item">
    <div class="ps-conversation-avatar">
        <a class="ps-avatar" href="#">
            <img width=32 src="<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjUDMzNXdIYzBoR3lkQzFSQnluZXVPaHN4cGVmNFpYcGVVTU0xU2ttNnQ4U1doQ1lUSzVQbmt5bTZGU0IwOWdXR2l6RHM2UFRWTU41bllJZkp6VFlHUzNydDJYUE8yNlcydU9TRFphYVZReWZPZHZEcVo3WWYwMWtHVlZxNStKK01kbnBZNnVwYVhGMUNXUHp5dUdabzZVajNTVjlNVkF5RlJBMndMTVhGYmtN*/ echo $user->get_avatar();?>" alt="<?php echo $user->get_fullname();?>" class="ps-name-tips ps-messages-currently-typing">
        </a>
    </div>
	<div class="ps-conversation-body">
        <div class="ps-conversation-user">
            <a href="#"><?php echo $user->get_fullname();?></a>
        </div>
		<!-- CSS typing indicator -->
		<div class="ps-typing-indicator ps-typing-indicator-small">
			<span></span>
			<span></span>
			<span></span>
		</div>
	</div>
</div>