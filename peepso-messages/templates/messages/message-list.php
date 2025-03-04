<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjUDMzNXdIYzBoR3lkQzFSQnluZXVPaXRlZ2ZsY3lrV01XencwL2p2d1VOTjRjc0I3WnVsTEtKYTVVUk9DalB5ZWpHcC9rbUZIR2dSY003SG5QT01PUTBROVQ0Rnk2L3pBUE1xTFB0TVhvaXJiMGQzbDY4aUxSNjA5U2NsZ1dxMDZZMlZvcjczbEx3U00zQ2xXZ3BnSVdGM2tETWVaVWtmQW9HZGxKZVhjRzBy*/

$PeepSoMessages = PeepSoMessages::get_instance();
$html = '';

while ($message = $PeepSoMessages->get_next_message()) {
	ob_start();
	$PeepSoMessages->show_message($message);
	$html .= ob_get_clean();
}

if (empty($html)) {
	$html = '<div class="pso-messages__info"><i class="pso-i-envelope"></i><span>' . __('No messages found.', 'msgso') . '</span></div>';
}

echo $html;
