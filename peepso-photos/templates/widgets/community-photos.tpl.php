<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjUDMzNXdIYzBoR3lkQzFSQnluZXVPaVd3bVZqOVNsUGdMQXkrR1djcEFkakJHZytqVWkyMnJDWmJxbURxVkt4R3h1clRtK1l6OW9vMG1wcTJuV2liVmE3WHNabDdtelFDZG12YnBzdTUwWjFBU2NNendQSi8vaVAycWc0OWhaZE5DMWFkNWRuMGgrT2MwVmcwVU5pejRCbG1FQ0d6ek0veXhzcURydURFekhl*/
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
        <div class="psw-photos">
        <?php
            if(count($instance['list']))
            {
        ?>
            <?php
                foreach ($instance['list'] as $photo)
                {
                    PeepSoTemplate::exec_template('photos', 'photo-item-widget', (array)$photo);
                }
            ?>
            <?php
                }
                else
                {
                    echo "<div class='psw-photos__info'>".__('No photos', 'picso')."</div>";
                }
            ?>
        </div>
    </div>
</div>

<?php

echo $args['after_widget'];

// EOF
