<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjUDMzNXdIYzBoR3lkQzFSQnluZXVPaSsza0hCVVNTMzI2YVNnbkRaaldHVUNHM0pXdkFRTDNxQ0Y2K3VITnN4VzdYdG12dmZKSEVLRE0ra25rVmRjdVhHT2hWS1pxOElWSlF1aXJ3em9nYVZBMkdHT0lQT1NQOHpVdFhMOEJiRzc4YVJrRkxxeXNMYmlYaitsbW1jQjk3akVWbjZPb0lsaEJ5QnZKWlc4OVAv*/
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

    </div>

<?php
echo $args['after_widget'];
// EOF
