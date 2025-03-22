<?php
/**
 * Fired during plugin activation
 */
class Map_Drawing_Assessment_Activator {

    /**
     * Activate the plugin
     */
    public static function activate() {
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

        // Add plugin version to options
        add_option('map_drawing_assessment_version', MAP_DRAWING_ASSESSMENT_VERSION);

        // Set default plugin options
        self::set_default_options();

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
        );

        foreach ($default_options as $key => $value) {
            add_option('map_drawing_assessment_' . $key, $value);
        }
    }
}