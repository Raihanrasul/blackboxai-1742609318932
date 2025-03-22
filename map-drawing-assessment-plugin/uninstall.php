<?php
/**
 * Fired when the plugin is uninstalled.
 */

// If uninstall not called from WordPress, then exit.
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete plugin tables
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
    'map_drawing_assessment_map_center_lng',
    'map_drawing_assessment_map_min_zoom',
    'map_drawing_assessment_map_max_zoom',
    'map_drawing_assessment_email_from_name',
    'map_drawing_assessment_email_from_address',
    'map_drawing_assessment_access_code_length',
    'map_drawing_assessment_access_token_expiry_days',
    'map_drawing_assessment_enable_debug_logging',
    'map_drawing_assessment_access_code_subject',
    'map_drawing_assessment_access_code_body',
    'map_drawing_assessment_results_subject',
    'map_drawing_assessment_results_body'
);

foreach ($options as $option) {
    delete_option($option);
}

// Delete user meta
$wpdb->query("
    DELETE FROM $wpdb->usermeta 
    WHERE meta_key IN (
        'map_assessment_access',
        'map_assessment_access_code',
        'map_assessment_access_expiry',
        'map_assessment_access_period'
    )
");

// Remove capabilities
$admin_role = get_role('administrator');
if ($admin_role) {
    $admin_role->remove_cap('manage_map_assessment');
    $admin_role->remove_cap('view_map_assessment');
}

// Delete plugin upload directory
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

// Clear any transients and caches
$wpdb->query("
    DELETE FROM $wpdb->options 
    WHERE option_name LIKE '_transient_map_drawing_%' 
    OR option_name LIKE '_transient_timeout_map_drawing_%'
");

// Clear rewrite rules
flush_rewrite_rules();