<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjUDMzNXdIYzBoR3lkQzFSQnluZXVPanllTFlTdXZGL3pjeE81TFNCZy9hSVlQdmJUNmJtYzBraHhlQzdaUFZzNEpWNkNYVmxiOU51dEE3WUcySHFLRXZtVTNwcCs0ZWxYeWVjSmVEQW9PbkFRS3V5bmpXSk9pWkltUFNkamNQQnNhRFR2MnpLWUNZeE9DOHJ0T05lU05zK1d3YitmSEhnQkVFMnU3NTZ0cm5m*/
    echo $args['before_widget'];
?>

<div class="ps-widget__wrapper<?php echo $instance['class_suffix'];?> ps-widget<?php echo $instance['class_suffix'];?>">
    <div class="ps-widget__header<?php echo $instance['class_suffix'];?>">
        <?php
            if ( ! empty( $instance['title'] ) ) {
                echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ). $args['after_title'];
            }
        ?>
    </div>


    <div class="ps-widget__body<?php echo $instance['class_suffix'];?>">
        <div class="psw-media">
        <?php
            if(count($instance['list']))
            {

              foreach ($instance['list'] as $video)
              {
                  $video = (array) $video;
                  echo PeepSoTemplate::exec_template('videos', 'video-item-widget', $video);
              }

            }
            else
            {
                if ($instance['media_type'] == 'all') {
                    echo "<div class='psw-media__info'>".__('No media', 'vidso')."</div>";
                } else {
                    if($instance['media_type'] == 'audio') {
                        echo "<div class='psw-media__info'>".__('No audio', 'vidso')."</div>";
                    } elseif ($instance['media_type'] == 'video') {
                        echo "<div class='psw-media__info'>".__('No video', 'vidso')."</div>";
                    }
                }
            }
        ?>
        </div>
    </div>
</div>

<?php

echo $args['after_widget'];

// EOF
