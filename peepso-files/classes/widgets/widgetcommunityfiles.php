<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjUDMzNXdIYzBoR3lkQzFSQnluZXVPaTBHamdGWGpVeXVGQXU4ajJGa1VpSnpYZUNGbmVJYnZrTFJIcUZnVi80U0p6Q1FoeEJRMHZoZ09HbUdueVZLOXkyV0J5NnJLT25YdGVaMkQ1clpEd2p6ZzZGZks3bWp6ejFoNnovdDRFL3hpS2ZocDBENG5rRnNLb3prcTMzcUFIN0E4c3BYckFtcXFxdVMwN0dmRXpH*/

class PeepSoWidgetCommunityFiles extends WP_Widget
{

    /**
     * Set up the widget name etc
     */
    public function __construct($id = null, $name = null, $args = null)
    {
        if (!$id) {
            $id = 'PeepSoWidgetCommunityFiles';
        }

        if (!$name) {
            $name = __('PeepSo Community Files', 'peepsofileuploads');
        }

        if (!$args) {
            $args = array('description' => __('PeepSo Community Files', 'peepsofileuploads'));
        }

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
    public function widget($args, $instance)
    {

        if (isset($instance['is_profile_widget'])) {
            // Override the HTML wrappers
            $args = apply_filters('peepso_widget_args_internal', $args);
        }

        // Additional shared adjustments
        $instance = apply_filters('peepso_widget_instance', $instance);

        if (!array_key_exists('template', $instance) || !strlen($instance['template'])) {
            $instance['template'] = 'community-files';
        }

        if (!array_key_exists('limit', $instance)) {
            $instance['limit'] = 6;
        }

        if (!array_key_exists('hideempty', $instance)) {
            $instance['hideempty'] = 0;
        }

        if (!array_key_exists('search_args', $instance)) {
            $instance['search_args'] = array(
                'number' => $instance['limit'],
            );
        }

        if (!array_key_exists('list', $instance) || !array_key_exists('total', $instance)) {
            $model = new PeepSoFilesModel();
            $files_args = [
                'limit' => $instance['limit'],
                'exclude_group_files' => TRUE,
                'exclude_page_files' => TRUE,
                'return_count' => TRUE
            ];
            $instance['total'] = $model->get_user_files($files_args);
        }

        if (0 == $instance['total'] && true == $instance['hideempty']) {
            return false;
        }

        wp_enqueue_script('peepso-modal-comments');

        PeepSoTemplate::exec_template('widgets', $instance['template'], array('args' => $args, 'instance' => $instance));
    }

    /**
     * Outputs the admin options form
     *
     * @param array $instance The widget options
     */
    public function form($instance)
    {

        $instance['fields'] = array(
            // general
            'limit' => true,
            'title' => true,

            // peepso
            'integrated' => true,
            'position' => true,
            'ordering' => true,
            'hideempty' => true,

        );

        if (!isset($instance['title'])) {
            $instance['title'] = __('Latest Files', 'peepsofileuploads');
        }

        $instance['limit'] = isset($instance['limit']) ? (int) $instance['limit'] : 6;

        $this->instance = $instance;

        $settings = apply_filters('peepso_widget_form', array('html' => '', 'that' => $this, 'instance' => $instance));
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
    public function update($new_instance, $old_instance)
    {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
        $instance['limit'] = isset($new_instance['limit']) ? (int) $new_instance['limit'] : 6;

        $instance['integrated'] = 1;
        $instance['hideempty'] = isset($new_instance['hideempty']) ? (int) $new_instance['hideempty'] : 0;
        $instance['position'] = isset($new_instance['position']) ? strip_tags($new_instance['position']) : 0;

        return $instance;
    }
}

// EOF
