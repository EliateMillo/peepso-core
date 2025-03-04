<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjUDMzNXdIYzBoR3lkQzFSQnluZXVPaHFhenBsRnkxWWF1ZGg0YUtiK3hRTGFxVUx0WkdrUWR5UUFKVzVNcHhaWVJLVC9qUWRrWXZPWlpRWXk2UHMyTEI5cHkyWWhSK3oxSERIM3hhNi8vd0d4SFVHK0N1V2ZVdzJpTSs1S2ZYVFZwcThoejk0b1RQWGNnZGREa25pMFVtRXdPTzN4T3RVNmhFdE1GZFRNREd5*/
$PeepSoPhotos = PeepSoPhotos::get_instance();
?>
<div class="ps-media__attachment ps-media__attachment--photos cstream-attachment ps-media-photos photo-container photo-container-placeholder ps-clearfix ps-js-photos">
	<?php $PeepSoPhotos->show_photo_comments($photo); ?>
	<div class="ps-loading ps-media-loading ps-js-loading">
		<div class="ps-spinner">
			<div class="ps-spinner-bounce1"></div>
			<div class="ps-spinner-bounce2"></div>
			<div class="ps-spinner-bounce3"></div>
		</div>
	</div>
</div>
