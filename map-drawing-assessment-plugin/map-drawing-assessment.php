<?php
/**
 * Plugin Name: Map Drawing Assessment
 * Plugin URI: https://theseru.co.uk
 * Description: A comprehensive map drawing assessment system with MCQ and fill in the blanks functionality
 * Version: 1.0.0
 * Author: SERU
 * Text Domain: map-drawing-assessment
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Plugin version
define('MAP_DRAWING_ASSESSMENT_VERSION', '1.0.0');
define('MAP_DRAWING_ASSESSMENT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('MAP_DRAWING_ASSESSMENT_PLUGIN_URL', plugin_dir_url(__FILE__));
define('MAP_DRAWING_CUSTOM_MAP_URL', 'https://theseru.co.uk/idealmap/maptiles/{z}/{x}/{y}.png');

/**
 * The code that runs during plugin activation
 */
function activate_map_drawing_assessment() {
    require_once MAP_DRAWING_ASSESSMENT_PLUGIN_DIR . 'includes/class-activator.php';
    Map_Drawing_Assessment_Activator::activate();
}

/**
 * The code that runs during plugin deactivation
 */
function deactivate_map_drawing_assessment() {
    require_once MAP_DRAWING_ASSESSMENT_PLUGIN_DIR . 'includes/class-deactivator.php';
    Map_Drawing_Assessment_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_map_drawing_assessment');
register_deactivation_hook(__FILE__, 'deactivate_map_drawing_assessment');

/**
 * The core plugin class
 */
require MAP_DRAWING_ASSESSMENT_PLUGIN_DIR . 'includes/class-map-drawing-assessment.php';

/**
 * Begins execution of the plugin
 */
function run_map_drawing_assessment() {
    $plugin = new Map_Drawing_Assessment();
    $plugin->run();
}
run_map_drawing_assessment();