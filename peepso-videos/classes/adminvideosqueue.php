<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjUDMzNXdIYzBoR3lkQzFSQnluZXVPaU9HNUxUU21zSlpNNnRhVnhYcUFJSytrbHFYcGN1cktqdlE2cmMzaWRVVER1SndjU1RNYnJrTERvU090RCtVV24zc2phVTBlZmVRR0ZjYXh2MFNNTWtDVzlBL3Jlc0o1K2hINzhCdGYyNUJoSEZwTTJPdnpFb3hSSGlOV2dicnJsTk13bEVrZGRyQzl4bm94Y2JiWXdk*/

class PeepSoAdminVideosQueue
{
	/** 
	 * DIsplays the table for managing the Request data queue.
	 */
	public static function administration()
	{

		$oPeepSoListTable = new PeepSoAdminVideosQueueListTable();
		$oPeepSoListTable->prepare_items();

		echo '<form id="form-request-data" method="post">';
		wp_nonce_field('bulk-action', 'videos-queue-nonce');
		$oPeepSoListTable->display();
		echo '</form>';


        echo PeepSoTemplate::exec_template('admin', 'queue-status-description');
	}
}