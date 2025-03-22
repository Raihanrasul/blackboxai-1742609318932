<?php
// If uninstall not called from WordPress, then exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete plugin options
$options = array(
    'map_drawing_assessment_version',
    'map_drawing_assessment_access_period_days',
    'map_drawing_assessment_enable_email_notifications',
    'map_drawing_assessment_enable_auto_submission',
    'map_drawing_assessment_max_attempts',
    'map_drawing_assessment_passing_score',
    'map_drawing_assessment_map_zoom_level',
    'map_drawing_assessment_map_center_lat',
    'map_drawing_assessment_map_center_lng'
);

foreach ($options as $option) {
    delete_option($option);
}

// Delete user meta
delete_metadata('user', 0, 'map_assessment_access', '', true);
delete_metadata('user', 0, 'map_assessment_access_code', '', true);
delete_metadata('user', 0, 'map_assessment_access_code_expiry', '', true);
delete_metadata('user', 0, 'map_assessment_access_period', '', true);
delete_metadata('user', 0, 'map_assessment_result_token', '', true);
delete_metadata('user', 0, 'map_assessment_result_token_expiry', '', true);
delete_metadata('user', 0, 'map_assessment_result_submission_id', '', true);

// Drop custom tables
global $wpdb;
$tables = array(
    $wpdb->prefix . 'map_drawing_questions',
    $wpdb->prefix . 'map_drawing_submissions',
    $wpdb->prefix . 'map_drawing_progress',
    $wpdb->prefix . 'map_drawing_answers'
);

foreach ($tables as $table) {
    $wpdb->query("DROP TABLE IF EXISTS $table");
}

// Delete uploaded files
$upload_dir = wp_upload_dir();
$plugin_upload_dir = $upload_dir['basedir'] . '/map-drawing-assessment';

if (file_exists($plugin_upload_dir)) {
    // Recursively delete directory and its contents
    function map_drawing_assessment_rrmdir($dir) {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (is_dir($dir . "/" . $object)) {
                        map_drawing_assessment_rrmdir($dir . "/" . $object);
                    } else {
                        unlink($dir . "/" . $object);
                    }
                }
            }
            rmdir($dir);
        }
    }
    map_drawing_assessment_rrmdir($plugin_upload_dir);
}

// Clear any remaining transients
delete_transient('map_drawing_assessment_cache');

// Clear rewrite rules
flush_rewrite_rules();