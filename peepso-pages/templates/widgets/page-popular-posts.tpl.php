<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjUDMzNXdIYzBoR3lkQzFSQnluZXVPaXJwR3VCV2ZlSVpYQUF5cGNKbGJYWmZ6ZVY4MHJNZnYrcjBDMkFrM05vWW1BL0JYd3pucWRmOU9vak94K3d3Kzc3VDljMW81SHBGTWVkMGlxVy9lc0d4ZnlMK0pra1o5ZTEvNlhyd3hqOTkzL0xrR1N0eGV3dThHRE1vQ2hYSjRYemZGTTdnNUJrekdEdk5STkQxdTgw*/
$limit = (isset($instance['limit']) && is_int($instance['limit'])) ? $instance['limit'] : 10;

// Set global variables to filter only page posts
$GLOBALS['peepso_page_only'] = TRUE;
$GLOBALS['peepso_remove_post_actions'] = TRUE;

if (isset($_GET['legacy-widget-preview'])) {
    PeepSo3_Mayfly::del('peepso_pages_widget_popular_posts');
}

$peepso_activity = new PeepSoActivity();
$peepso_activity->post_query = PeepSo3_Mayfly::get_or_set_if_empty('peepso_pages_widget_popular_posts', HOUR_IN_SECONDS, function() use ($peepso_activity, $limit) {
    return $peepso_activity->get_posts(NULL, NULL, NULL, NULL, FALSE, $limit);
});

unset($GLOBALS['peepso_page_only']);

if ($peepso_activity->post_query->posts) {
    echo $args['before_widget'];

    ?><div class="ps-widget__wrapper<?php echo $instance['class_suffix'];?> ps-widget<?php echo $instance['class_suffix'];?>">
        <div class="ps-widget__header<?php echo $instance['class_suffix'];?>">
            <?php
            if ( ! empty( $instance['title'] ) ) {
                echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ). $args['after_title'];
            }
            ?>
        </div>

        <div class="ps-widget__body">
            <div class="psw-pages">
                <?php
                    add_filter('peepso_commentsbox_display', '__return_false');
                    add_filter('peepso_show_post_options', '__return_false');
                    add_filter('peepso_show_recent_comments', '__return_false');
                    add_filter('peepso_pages_update_post_title', '__return_true');

                    ob_start();
                    while ($peepso_activity->next_post()) {
                        $peepso_activity->show_post();
                    }
                    $html = ob_get_clean();

                    // Remove the classes to prevent postbox from showing when editing the post
                    $html = preg_replace('/ps-js-activity--\d+\s*/', '', $html);

                    echo $html;

                    remove_filter('peepso_commentsbox_display', '__return_false');
                    remove_filter('peepso_show_post_options', '__return_false');
                    remove_filter('peepso_show_recent_comments', '__return_false');
                    remove_filter('peepso_pages_update_post_title', '__return_true');
                ?>
            </div>
        </div>
    </div><?php

    echo $args['after_widget'];
}

unset($GLOBALS['peepso_remove_post_actions']);
// EOF
