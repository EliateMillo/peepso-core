<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjUDMzNXdIYzBoR3lkQzFSQnluZXVPaUJhc2NiaU94aVZsRWszdGg3S1d4eWtScnVhWnpBLzZoVVVPTFVqZEg2dE4xQVRTNFIrc0lwdElBNDhiZjJtTVFtbExVbEJJYzBVcHgrWGxVb09ubi9HekdLd1lFS3orZmx5TWpRdDZOakFOZ3pxTENjc2YrUXlQdkQwSVhZRHo0QU9lVWo5NW9PMTZQQURMWFhCYWdv*/
if(class_exists('PeepSoMaintenanceFactory')) {
    class PeepSoMaintenancePages extends PeepSoMaintenanceFactory
    {
        public static function deletePageCategoriesForDeletedPages()
        {
            global $wpdb;

            $t1 = $wpdb->prefix . PeepSoPageCategoriesPages::TABLE;
            $t2 = $wpdb->posts;
            $query = "DELETE FROM $t1 WHERE NOT EXISTS(SELECT `ID` FROM $t2 WHERE $t2.ID=$t1.pm_page_id)";
            $wpdb->query($query);
        }

        public static function deletePageCategoriesForDeletedCategories()
        {
            global $wpdb;

            $t1 = $wpdb->prefix . PeepSoPageCategoriesPages::TABLE;
            $t2 = $wpdb->posts;
            $query = "DELETE FROM $t1 WHERE NOT EXISTS(SELECT `ID` FROM $t2 WHERE $t2.ID=$t1.pm_cat_id)";
            $wpdb->query($query);
        }

        public static function recountPageCategories()
        {
            $PeepSoPageCategories = new PeepSoPageCategories();

            foreach ($PeepSoPageCategories->categories as $id => $category) {
                PeepSoPageCategoriesPages::update_stats_for_category($id);
            }
        }

        public static function deleteMembersForDeletedPages()
        {
            global $wpdb;
            // Orphaned page_members entries for deleted pages
            $t1 = $wpdb->prefix . PeepSoPageUsers::TABLE;
            $t2 = $wpdb->posts;
            $query = "DELETE FROM $t1 WHERE NOT EXISTS(SELECT `ID` FROM $t2 WHERE $t2.ID=$t1.pm_page_id)";
            $wpdb->query($query);
        }

        public static function deleteMembersForDeletedUsers()
        {
            global $wpdb;

            // Orphaned page_members entries for deleted users
            $t1 = $wpdb->prefix . PeepSoPageUsers::TABLE;
            $t2 = $wpdb->users;
            $query = "DELETE FROM $t1 WHERE NOT EXISTS(SELECT `ID` FROM $t2 WHERE $t2.ID=$t1.pm_user_id)";
            $wpdb->query($query);
        }

        public static function rebuildPageFollowers()
        {
            return PeepSoPageFollowers::rebuild(50);
        }

        public static function deleteNotificationsForDeletedPages()
        {
            global $wpdb;
            // Orphaned notifications for deleted pages
            $t1 = $wpdb->prefix.PeepSoNotifications::TABLE;
            $t2 = $wpdb->posts;
            $query = "DELETE FROM $t1 WHERE $t1.not_module_id=".PeepSoPagesPlugin::MODULE_ID." AND NOT EXISTS(SELECT `ID` FROM $t2 WHERE $t2.ID=$t1.not_external_id)";
            $wpdb->query($query);
        }

        public static function deletePostsForDeletedPages() 
        {
            global $wpdb;

            $query = "SELECT ID FROM $wpdb->posts 
                LEFT JOIN $wpdb->postmeta ON $wpdb->postmeta.post_id = $wpdb->posts.ID 
                WHERE $wpdb->postmeta.meta_key = 'peepso_page_id' 
                    AND $wpdb->postmeta.meta_value IS NOT NULL 
                    AND not exists(SELECT ID FROM $wpdb->posts WHERE ID = $wpdb->postmeta.meta_value AND post_type='".PeepSoPage::POST_TYPE."')";
            $result = $wpdb->get_results($query);
            if ($result) {
                $activity = new PeepSoActivity();
                foreach ($result as $act) {
                    $activity->delete_post($act->ID);
                }
            }

            return $result;
        }
    }
}