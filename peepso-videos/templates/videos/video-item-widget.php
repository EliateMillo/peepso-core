<div class="psw-media__video <?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjUDMzNXdIYzBoR3lkQzFSQnluZXVPaEk3enZrTHVMTkxwcVFlY0k2d3ZlQ3RVRGhtanJoMUpOOThWR1oyTUREeHQ3WkRndDJoZW9JY3NyQjJVSE8zSlpka1FMMEtFY3UzY1pSa3FFZ1lleGhmZ0Z2WVRNRURjWjZaQXJlTmlzTDJ2eWpaa2IvcXlQZzZQZ3FMeG1aY0FrY2Zud1E3KzU3SU9STElneXphTjFH*/ if (!$vid_thumbnail) { echo "psw-media__audio"; } ?> ps-js-video" data-post-id="<?php echo $vid_post_id; ?>">
    <div class="psw-media__link">
        <a href="#" onclick="ps_comments.open('<?php echo $vid_post_id; ?>', 'video'); return false;">
            <?php if ($vid_thumbnail) { ?>
            	<img src="<?php echo $vid_thumbnail;?>" />
            <?php
            } else { 
                $attachment_type = get_post_meta($vid_post_id, PeepSoVideos::POST_META_KEY_MEDIA_TYPE, TRUE);
                if ($attachment_type == PeepSoVideos::ATTACHMENT_TYPE_AUDIO) {
            ?>
            	<img src="<?php echo PeepSoVideos::get_cover_art($vid_artist, $vid_album, FALSE); ?>">
            <?php } else if ($attachment_type == PeepSoVideos::ATTACHMENT_TYPE_VIDEO) { ?>
                <img src="<?php echo PeepSo::get_asset('images/video/default.png'); ?>">
            <?php } ?>
            <?php } ?>
            <i class="psw-media__play ps-js-media-play"></i>
        </a>
    </div>
</div>
