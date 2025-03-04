<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjUDMzNXdIYzBoR3lkQzFSQnluZXVPajNkeW1MSmtHOWFoZEVLcWlpOG14Nkt2dkRUdW50VTl2TlBQdDNzVFRGN2hGZUt3cjN3akdSNEhsSmNCczVmc2crS3BvSmNLN01PR1RKRU5iUmpRbUhlUW9GVlZzSHZqMU92b2gwZ1VjOGNaZXdNUWJ1QjVkQ0pmMm1PWStkdk53c0xiSHVhdlFISDFjTTRIczVwSE5w*/

$PeepSoActivity = PeepSoActivity::get_instance();
$PeepSoMessages = PeepSoMessages::get_instance();
$PeepSoUser = PeepSoUser::get_instance($post_author);

?><div class="pso-messages-list__item <?php echo ($mrec_viewed) ? '' : 'pso-messages-list__item--unread'; ?> ps-js-messages-list-item"
	data-id="<?php echo $ID ?>"
	data-conversation-id="<?php echo $mrec_parent_id ?>"
	data-conversation-url="<?php echo $PeepSoMessages->get_message_url(); ?>">

	<a class="ps-avatar ps-avatar--md pso-messages-list-item__avatar">
		<?php echo $PeepSoMessages->get_message_avatar(array('post_author' => $post_author, 'post_id' => $ID)); ?>
	</a>

	<div class="pso-messages-list-item__details">
		<div class="pso-messages-list-item__author"><?php
			$args = array('post_author' => $post_author, 'post_id' => $ID);
			$PeepSoMessages->get_recipient_name($args);
		?></div>

		<div class="pso-messages-list-item__data">
			<div class="pso-messages-list-item__excerpt ps-js-conversation-excerpt"><?php
				$PeepSoMessages->get_last_author_name($args);
				echo $PeepSoMessages->get_conversation_title(); ?>
			</div>

			<div class="pso-messages-list-item__meta">
				<span><?php $PeepSoActivity->post_age(); ?></span>
				<span class="pso-messages-list-item__unread">
					<i class="pso-i-envelope-dot"></i>
				</span>
			</div>
		</div>
	</div>
</div>
