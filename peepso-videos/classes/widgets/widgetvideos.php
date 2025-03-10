<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjUDMzNXdIYzBoR3lkQzFSQnluZXVPaFRuVXI1S3ZmL0p5QURuMzNJWVY1a2tvTHlBRkY1TU1WRnBBS0wrTnZpU0dxK3dkeEVhQWl4YjVOb3AvNDhjbloxMjM3NjZXb3JBWFExZEs4cGJTY1grOCs3ZTBKcUFKbjZwK2ovczNIZTJSMUN5cE1UZnBnYTJRWG1SNDErYmJSdU5LbWcyK0hOWk9OemRsTmNVMTZh*/


class PeepSoWidgetVideos extends WP_Widget
{

    /**
     * Set up the widget name etc
     */
    public function __construct($id = null, $name = null, $args= null) {
        if(!$id)    $id     = 'PeepSoWidgetVideos';
        if(!$name)  $name   = __( 'PeepSo Audio & Video', 'vidso' );
        if(!$args)  $args   = array( 'description' => __( 'PeepSo Audio & Video Widget', 'vidso' ), );

        parent::__construct(
           $id, // Base ID
           $name, // Name
           $args // Args
        );
    }

    /**
     * Outputs the content of the widget
     *
     * @param array $args
     * @param array $instance
     */
    public function widget( $args, $instance ) {

        $view_id = get_current_user_id();

        if(isset($instance['is_profile_widget']))
        {
            // Use currently viewed profile
            $view_id = PeepSoProfileShortcode::get_instance()->get_view_user_id();

            // Override the HTML wrappers
            $args = apply_filters('peepso_widget_args_internal', $args);
        }

        // Additional shared adjustments
        $instance = apply_filters('peepso_widget_instance', $instance);

        if(!array_key_exists('template', $instance) || !strlen($instance['template']))
        {
            $instance['template'] = 'videos.tpl';
        }

        if(!array_key_exists('user_id', $instance))
        {
            $instance['user_id'] = $view_id;
        }

        if(!array_key_exists('limit', $instance)) {
            $instance['limit'] = 12;
        }

        if (!array_key_exists('hideempty', $instance)) {
            $instance['hideempty'] = 0;
        }

        if (!array_key_exists('media_type', $instance)) {
            $instance['media_type'] = 'all';
        }

        if(!array_key_exists('search_args', $instance))
        {
            $instance['search_args'] = array(
                'number' => $instance['limit'],
            );
        }

        // @TODO guest should be able to see the videos of another person if privacy allows it
        if(!$instance['user_id'])
        {
            return false;
        }

        if(!array_key_exists('list', $instance) || !array_key_exists('total', $instance))
        {
            if (isset($_GET['legacy-widget-preview'])) {
                PeepSoVideos::delete_widget_mayfly($view_id);
            }

            $media_type = isset($instance['media_type']) && !empty($instance['media_type']) ? $instance['media_type'] : 'all';

            $videosModel = new PeepSovideosModel();
            
            $instance['list'] = PeepSo3_Mayfly::get_or_set_if_empty('peepso_videos_widget_' . $view_id, HOUR_IN_SECONDS, function() use ($videosModel, $view_id, $media_type, $instance) {
                return $videosModel->get_user_videos($view_id, $media_type,0, $instance['limit']);
            });

            $instance['total'] = PeepSo3_Mayfly::get_or_set_if_empty('peepso_videos_widget_num_' . $view_id, HOUR_IN_SECONDS, function() use ($videosModel, $view_id, $media_type) {
                return $videosModel->get_num_videos($view_id, $media_type);
            });
        }

        if(0==$instance['total'] && true == $instance['hideempty']) {
            return FALSE;
        }

        PeepSoTemplate::exec_template( 'widgets', $instance['template'], array( 'args'=>$args, 'instance' => $instance ) );
        wp_enqueue_script('peepsovideos');
    }

    /**
     * Outputs the admin options form
     *
     * @param array $instance The widget options
     */
    public function form( $instance ) {

        $instance['fields'] = array(
            // general
            'limit'     => TRUE,
            'title'     => TRUE,

            // peepso
            'integrated'=> TRUE,
            'position'  => TRUE,
            'ordering'  => TRUE,
            'hideempty' => TRUE,

        );

		if (!isset($instance['title'])) {
			$instance['title'] = __('My Media', 'vidso');
		}

        $this->instance = $instance;

        $settings =  apply_filters('peepso_widget_form', array('html'=>'', 'that'=>$this,'instance'=>$instance));

        ob_start();

        $media_type = !empty($instance['media_type']) ? $instance['media_type'] : 'all';
        ?>
        <h3><?php echo __('Advanced','vidso');?></h3>
        <p>
            <label for="<?php echo $this->get_field_id('media_type'); ?>">
                <?php echo __('Media type', 'vidso'); ?>
                <select class="widefat" id="<?php echo $this->get_field_id('media_type'); ?>"
                        name="<?php echo $this->get_field_name('media_type'); ?>">
                    <option value="all"><?php echo __('All', 'vidso'); ?></option>
                    <option value="video" <?php if('video' === $media_type) echo ' selected="selected" ';?>><?php echo __('Video', 'vidso'); ?></option>
                    <option value="audio" <?php if('audio' === $media_type) echo ' selected="selected" ';?>><?php echo __('Audio', 'vidso'); ?></option>
                </select>
            </label>
        </p>
        <?php
        $settings['html'] .= ob_get_clean();
        echo $settings['html'];
    }

    /**
     * Sanitize widget form values as they are saved.
     *
     * @see WP_Widget::update()
     * @param array $new_instance Values just sent to be saved.
     * @param array $old_instance Previously saved values from database.
     *
     * @return array Updated safe values to be saved.
     */
    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['title']       = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
        $instance['limit']       = isset($new_instance['limit']) ? (int) $new_instance['limit'] : 12;
        $instance['hideempty']   = isset($new_instance['hideempty']) ? (int) $new_instance['hideempty'] : 0;
        $instance['position']    = isset($new_instance['position']) ? strip_tags($new_instance['position']) : 0;
        $instance['media_type']  = isset($new_instance['media_type']) ? $new_instance['media_type'] : 'all';

        return $instance;
    }
}

// EOF
