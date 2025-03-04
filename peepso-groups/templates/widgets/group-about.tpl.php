<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjUDMzNXdIYzBoR3lkQzFSQnluZXVPamZVc0tnaFJyT3IrZ3BpSERtZFg4L00rYmNKSitINGR6M0pDM0JYQm5DYkVzVkgxTkRkenhiaWpzbXo2ZGJ0djc5RHRjQnIyR3VsNmlpVkVMOHErMmR1bDV5ZVJQKzFuNjVtQ3BHZ2tLMEZRYVNqeDhJdWRuUUdxaEF3ak9TZmVOaFVFU1dUVmwyejJXdW80SjI2bmRt*/
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
