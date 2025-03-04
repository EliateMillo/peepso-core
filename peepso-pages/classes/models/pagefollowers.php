<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjUDMzNXdIYzBoR3lkQzFSQnluZXVPZ0laNks0b1VUNFFOMktkN3VhaE4yWGNtRitXVnA2OHJXNlhQeWUxb0lQS0c2czVSNWxvdW5zNTlBdkdtT1RpYmJ1V0FCZnEyQ3k3OUxTZEVrZDRRcUkyWW9NZENaRUY2OTJ2d3ZuTlFRcHB5SUsyNis1QjJzbXlhVm1zZFFrUTk5cStEU2ZrN2FhYVV6QWxKejF1emc1*/

class PeepSoPageFollowers
{
    const TABLE = 'peepso_page_followers';

    public $followers = array();

    public function __construct($page_id, $load_objects = false, $follow = NULL, $notify = NULL, $email = NULL)
    {
        global $wpdb;
        $page_id = intval($page_id);

        $this->followers = array();

        $where = '';

        if(!is_null($follow) && in_array($follow, array(0,1))) {
            $where.= " AND pf_follow=$follow ";
        }

        if(!is_null($notify) && in_array($notify, array(0,1))) {
            $where.= " AND pf_notify=$notify";
        }

        if(!is_null($email) && in_array($email, array(0,1))) {
            $where.= " AND pf_email=$email";
        }

        $r = $wpdb->get_results("SELECT pf_user_id FROM  ". $wpdb->prefix . PeepSoPageFollowers::TABLE . " WHERE pf_page_id=$page_id $where");

        if (count($r)) {
            foreach ($r as $gf) {
                if($load_objects ) {
                    $this->followers[$gf->pf_user_id] = new PeepSoPageFollower($page_id, $gf->pf_user_id);
                } else {
                    $this->followers[$gf->pf_user_id] = $gf->pf_user_id;
                }
            }
        }
    }


    public function get_followers() {
        return $this->followers;
    }


    public static function rebuild($limit = 10)
    {
        global $wpdb;
        $r = $wpdb->get_results("SELECT `pm_page_id` as p, `pm_user_id` as u FROM " . $wpdb->prefix . PeepSoPageUsers::TABLE . " peepso_page_member 
            LEFT JOIN " . $wpdb->prefix . PeepSoPageFollowers::TABLE . " ON pf_page_id=peepso_page_member.pm_page_id AND pf_user_id=peepso_page_member.pm_user_id
            WHERE `pm_user_status` LIKE 'member%' AND pf_user_id IS NULL LIMIT 0,$limit");
        $i =0;
        if (count($r)) {
            foreach ($r as $gm) {
                new PeepSoPageFollower($gm->p, $gm->u);
                $i ++;
            }
        }

        return $i;
    }
}