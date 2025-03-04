<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjUDMzNXdIYzBoR3lkQzFSQnluZXVPaEk4OWxTdytjWUh4eDRYK3ZVZHBTN1NWbWkxbmJyOGh2ZGJCUVREMFUwK2VCMVBwdDBHSEdKOXIvMWhsWjRXUFJyZ3dEbE9nNkNCa0hQekMvOW43aUZjTGQrckcyVHZYUithdVRYRStxTlBRaGZVd2JJUUlHRWRwL3Ywd3FHTGpGN3dWUFo1aVAyQmVwanYwZjFxekRm*/

wp_enqueue_script('peepso-photos');
wp_enqueue_script('peepso-photos-widget');

?><div class="psw-photos__photo ps-js-photo" data-post-id="<?php echo $pho_post_id; ?>">
	<a class="psw-photos__photo-link" data-id="<?php echo $act_id; ?>" href="#" rel="post-<?php echo $pho_post_id;?>"
			onclick="ps_comments.open('<?php echo $pho_id ?>', 'photo', { <?php
				echo 'nonav: () => ps_widget.nonav(this), ';
				echo 'prev: () => ps_widget.prev(this), ';
				echo 'next: () => ps_widget.next(this)';
			?> }); return false;">
		<img src="<?php echo $pho_thumbs['s_s']; ?>" alt="<?php echo $pho_orig_name;?>" />
	</a>
</div>
