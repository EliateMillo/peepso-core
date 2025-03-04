<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjUDMzNXdIYzBoR3lkQzFSQnluZXVPaXlGR3VrcEg1NTk2c1JBRUxQUWdWNFdzSytuQ1ZsY2lsVmJEMnc3SC9taVB1TWhMdUoxampBdC9XNFd2NW5UcmFaRGlEaUFPSkFmbFd1eFFNbG9SYklzcXpLSDVxdWd0aE5scDZSZEdTUDFMT1NzTGFCcDhSV2RLTFkwQnlmRENGc3FIK1Z2NE1uMUxyNld6N1JkazNH*/


class PeepSoWidgetPhotos extends WP_Widget
{

    /**
     * Set up the widget name etc
     */
    public function __construct($id = null, $name = null, $args= null) {
        if(!$id)    $id     = 'PeepSoWidgetPhotos';
        if(!$name)  $name   = __( 'PeepSo Photos', 'picso' );
        if(!$args)  $args   = array( 'description' => __( 'PeepSo Photos Widget', 'picso' ), );

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
            $instance['template'] = 'photos.tpl';
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

        if(!array_key_exists('search_args', $instance))
        {
            $instance['search_args'] = array(
                'number' => $instance['limit'],
            );
        }

        // @TODO guest should be able to see the photos of another person if privacy allows it
        if(!$instance['user_id'])
        {
            return false;
        }

        if(!array_key_exists('list', $instance) || !array_key_exists('total', $instance))
        {
            if (isset($_GET['legacy-widget-preview'])) {
                PeepSoSharePhotos::delete_widget_mayfly($view_id);
            }

            $photosModel = new PeepSoPhotosModel();

            $instance['list'] = PeepSo3_Mayfly::get_or_set_if_empty('peepso_photos_widget_' . $view_id, HOUR_IN_SECONDS, function() use ($photosModel, $instance, $view_id) {
                return $photosModel->get_user_photos($view_id, 0, $instance['limit']);
            });

            $instance['total'] = PeepSo3_Mayfly::get_or_set_if_empty('peepso_photos_widget_num_' . $view_id, HOUR_IN_SECONDS, function() use ($photosModel, $view_id) {
                return $photosModel->get_num_photos($view_id);
            });
        }

        if(0==$instance['total'] && true == $instance['hideempty']) {
            return FALSE;
        }

        wp_enqueue_script('peepso-modal-comments');

        PeepSoTemplate::exec_template( 'widgets', $instance['template'], array( 'args'=>$args, 'instance' => $instance ) );
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
            'integrated'   => TRUE,
            'position'  => TRUE,
            'ordering'  => TRUE,
            'hideempty' => TRUE,

        );

		if (!isset($instance['title'])) {
			$instance['title'] = __('My Photos', 'picso');
		}
		
        $this->instance = $instance;

        $settings =  apply_filters('peepso_widget_form', array('html'=>'', 'that'=>$this,'instance'=>$instance));
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

        $instance['integrated']  = 1;
        $instance['hideempty']   = isset($new_instance['hideempty']) ? (int) $new_instance['hideempty'] : 0;
        $instance['position']    = isset($new_instance['position']) ? strip_tags($new_instance['position']) : 0;

        return $instance;
    }
}

// EOF