<?php
/**
 * The core plugin class
 */
class Map_Drawing_Assessment {

    protected $loader;
    protected $plugin_name;
    protected $version;

    public function __construct() {
        $this->version = MAP_DRAWING_ASSESSMENT_VERSION;
        $this->plugin_name = 'map-drawing-assessment';
        
        $this->load_dependencies();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    private function load_dependencies() {
        require_once MAP_DRAWING_ASSESSMENT_PLUGIN_DIR . 'includes/class-loader.php';
        require_once MAP_DRAWING_ASSESSMENT_PLUGIN_DIR . 'includes/class-admin.php';
        require_once MAP_DRAWING_ASSESSMENT_PLUGIN_DIR . 'includes/class-public.php';
        require_once MAP_DRAWING_ASSESSMENT_PLUGIN_DIR . 'includes/class-questions.php';
        require_once MAP_DRAWING_ASSESSMENT_PLUGIN_DIR . 'includes/class-submissions.php';
        require_once MAP_DRAWING_ASSESSMENT_PLUGIN_DIR . 'includes/class-email.php';

        $this->loader = new Map_Drawing_Assessment_Loader();
    }

    private function define_admin_hooks() {
        $plugin_admin = new Map_Drawing_Assessment_Admin($this->get_plugin_name(), $this->get_version());

        // Admin menu and pages
        $this->loader->add_action('admin_menu', $plugin_admin, 'add_plugin_admin_menu');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');

        // AJAX handlers for admin
        $this->loader->add_action('wp_ajax_save_map_question', $plugin_admin, 'handle_save_map_question');
        $this->loader->add_action('wp_ajax_save_mcq_question', $plugin_admin, 'handle_save_mcq_question');
        $this->loader->add_action('wp_ajax_save_blank_question', $plugin_admin, 'handle_save_blank_question');
        $this->loader->add_action('wp_ajax_handle_bulk_action', $plugin_admin, 'handle_bulk_action');
        $this->loader->add_action('wp_ajax_save_correction', $plugin_admin, 'handle_save_correction');
        $this->loader->add_action('wp_ajax_send_result_email', $plugin_admin, 'handle_send_result_email');
    }

    private function define_public_hooks() {
        $plugin_public = new Map_Drawing_Assessment_Public($this->get_plugin_name(), $this->get_version());

        // Enqueue public assets
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');

        // Register shortcode
        $this->loader->add_action('init', $plugin_public, 'register_shortcodes');

        // AJAX handlers for public
        $this->loader->add_action('wp_ajax_save_drawing', $plugin_public, 'handle_save_drawing');
        $this->loader->add_action('wp_ajax_submit_assessment', $plugin_public, 'handle_submit_assessment');
        $this->loader->add_action('wp_ajax_validate_access_code', $plugin_public, 'handle_validate_access_code');
    }

    public function run() {
        $this->loader->run();
    }

    public function get_plugin_name() {
        return $this->plugin_name;
    }

    public function get_version() {
        return $this->version;
    }

    public function get_loader() {
        return $this->loader;
    }
}