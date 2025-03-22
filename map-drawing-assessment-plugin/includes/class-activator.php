<?php
/**
 * Fired during plugin activation
 */
class Map_Drawing_Assessment_Activator {

    /**
     * Activate the plugin
     */
    public static function activate() {
        global $wpdb;

        // Create required database tables
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-questions.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-submissions.php';

        Map_Drawing_Assessment_Questions::create_table();
        Map_Drawing_Assessment_Submissions::create_tables();

        // Create required directories
        $upload_dir = wp_upload_dir();
        $plugin_upload_dir = $upload_dir['basedir'] . '/map-drawing-assessment';
        
        if (!file_exists($plugin_upload_dir)) {
            wp_mkdir_p($plugin_upload_dir);
        }

        // Add capabilities
        $admin_role = get_role('administrator');
        if ($admin_role) {
            $admin_role->add_cap('manage_map_assessment');
            $admin_role->add_cap('view_map_assessment');
        }

        // Set default options
        self::set_default_options();

        // Add plugin version to options
        add_option('map_drawing_assessment_version', MAP_DRAWING_ASSESSMENT_VERSION);

        // Clear permalinks
        flush_rewrite_rules();
    }

    /**
     * Set default plugin options
     */
    private static function set_default_options() {
        $default_options = array(
            'access_period_days' => 30,
            'enable_email_notifications' => true,
            'enable_auto_submission' => true,
            'max_attempts' => 3,
            'passing_score' => 70,
            'map_zoom_level' => 13,
            'map_center_lat' => 51.5074,
            'map_center_lng' => -0.1278,
            'map_min_zoom' => 5,
            'map_max_zoom' => 19,
            'email_from_name' => get_bloginfo('name'),
            'email_from_address' => get_bloginfo('admin_email'),
            'access_code_length' => 8,
            'access_token_expiry_days' => 2,
            'enable_debug_logging' => false
        );

        foreach ($default_options as $key => $value) {
            add_option('map_drawing_assessment_' . $key, $value);
        }

        // Email templates
        $email_templates = array(
            'access_code_subject' => __('Your Map Assessment Access Code', 'map-drawing-assessment'),
            'access_code_body' => __(
                "Hello,\n\n" .
                "Your access code for the map assessment is: {access_code}\n\n" .
                "This code will expire in {expiry_days} days.\n\n" .
                "Please visit {assessment_url} to start your assessment.\n\n" .
                "Best regards,\n{site_name}",
                'map-drawing-assessment'
            ),
            'results_subject' => __('Your Map Assessment Results', 'map-drawing-assessment'),
            'results_body' => __(
                "Hello,\n\n" .
                "Your map assessment has been reviewed.\n\n" .
                "Total Score: {total_score}\n" .
                "Time Taken: {time_taken}\n\n" .
                "You can view your detailed results here: {results_url}\n" .
                "This link will expire in {expiry_days} days.\n\n" .
                "Best regards,\n{site_name}",
                'map-drawing-assessment'
            )
        );

        foreach ($email_templates as $key => $value) {
            add_option('map_drawing_assessment_' . $key, $value);
        }
    }
}