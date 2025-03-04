<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjUDMzNXdIYzBoR3lkQzFSQnluZXVPZzVoWmc4MER0Mng3Z1RsbjdCZFNVL3M0a0Ntb0VzUldXTll3aFE4ak1VK0JWMllwTDJVSU1PSGhwQWRnaUFhb1h5TXdKakxXVUlsYXppRGYvWU10YThBMnRHMFlpM1JUeFl6YmRrSW1QcThqd0RIRTdMTFYyblM4KzhVUGZjcGNyMmJuWmNOVk5ZRVZhN1BKdld0a0Nm*/

if(class_exists('PeepSoMaintenanceFactory')) {
    class PeepSoMaintenanceChat extends PeepSoMaintenanceFactory
    {
    }
}

//global $wpdb;
//
//// Delete messages sent
//$wpdb->delete(
//    $wpdb->posts,
//    array('post_author'=>$id, 'post_type' => PeepSoMessagesPlugin::CPT_MESSAGE),
//    array('%d','%s')
//);
//
//// Delete inline messages (eg joined, left)
//$wpdb->delete(
//    $wpdb->posts,
//    array('post_author'=>$id, 'post_type' => PeepSoMessagesPlugin::CPT_MESSAGE_INLINE_NOTICE),
//    array('%d','%s')
//);
//
//// Delete message recipients
//$wpdb->delete(
//    $wpdb->prefix.PeepSoMessageRecipients::TABLE,
//    array('mrec_user_id'=>$id),
//    array('%d','%s')
//);
//
//$wpdb->delete(
//    $wpdb->prefix.PeepSoMessageParticipants::TABLE,
//    array('mpart_user_id'=>$id),
//    array('%d','%s')
//);
