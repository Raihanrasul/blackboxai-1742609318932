<?php
/**
 * The admin-specific functionality of the plugin
 */
class Map_Drawing_Assessment_Admin {

    private $plugin_name;
    private $version;

    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;

        // Register AJAX handlers
        add_action('admin_init', array($this, 'register_ajax_handlers'));
    }

    /**
     * Register the stylesheets for the admin area.
     */
    public function enqueue_styles() {
        // jQuery UI styles
        wp_enqueue_style('jquery-ui', '//code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css');
        
        // Leaflet CSS
        wp_enqueue_style('leaflet', 'https://unpkg.com/leaflet@1.7.1/dist/leaflet.css');
        
        // Admin styles
        wp_enqueue_style($this->plugin_name, plugin_dir_url(dirname(__FILE__)) . 'assets/css/admin.css', array(), $this->version, 'all');
    }

    /**
     * Register the JavaScript for the admin area.
     */
    public function enqueue_scripts() {
        // jQuery and jQuery UI
        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-tooltip');
        
        // Leaflet
        wp_enqueue_script('leaflet', 'https://unpkg.com/leaflet@1.7.1/dist/leaflet.js', array('jquery'), '1.7.1', true);
        
        // Admin script
        wp_enqueue_script($this->plugin_name, plugin_dir_url(dirname(__FILE__)) . 'assets/js/admin.js', array('jquery', 'jquery-ui-core', 'jquery-ui-tooltip', 'leaflet'), $this->version, true);

        // Localize script
        wp_localize_script($this->plugin_name, 'mapDrawingAdmin', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('map_drawing_admin_nonce'),
            'customMapUrl' => MAP_DRAWING_CUSTOM_MAP_URL,
            'i18n' => array(
                'errorMessage' => __('An error occurred. Please try again.', 'map-drawing-assessment'),
                'confirmRevoke' => __('Are you sure you want to revoke access?', 'map-drawing-assessment'),
                'selectItemsMessage' => __('Please select items to process.', 'map-drawing-assessment'),
                'confirmBulkAction' => __('Are you sure you want to perform this action?', 'map-drawing-assessment')
            )
        ));
    }

    /**
     * Register AJAX handlers
     */
    public function register_ajax_handlers() {
        // Question management
        add_action('wp_ajax_save_map_question', array($this, 'handle_save_map_question'));
        add_action('wp_ajax_save_mcq_question', array($this, 'handle_save_mcq_question'));
        add_action('wp_ajax_save_blank_question', array($this, 'handle_save_blank_question'));
        add_action('wp_ajax_handle_bulk_action', array($this, 'handle_bulk_action'));

        // User management
        add_action('wp_ajax_grant_user_access', array($this, 'handle_grant_user_access'));
        add_action('wp_ajax_revoke_user_access', array($this, 'handle_revoke_user_access'));
    }

    /**
     * AJAX handlers
     */
    public function handle_save_map_question() {
        // Verify nonce
        if (!check_ajax_referer('map_drawing_admin_nonce', 'nonce', false)) {
            wp_send_json_error(__('Invalid security token. Please refresh the page and try again.', 'map-drawing-assessment'));
        }

        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'map-drawing-assessment'));
        }

        // Process the request
        $questions = new Map_Drawing_Assessment_Questions();
        $result = $questions->save_map_question($_POST);

        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }

        wp_send_json_success();
    }

    public function handle_save_mcq_question() {
        if (!check_ajax_referer('map_drawing_admin_nonce', 'nonce', false)) {
            wp_send_json_error(__('Invalid security token. Please refresh the page and try again.', 'map-drawing-assessment'));
        }

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'map-drawing-assessment'));
        }

        $questions = new Map_Drawing_Assessment_Questions();
        $result = $questions->save_mcq_question($_POST);

        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }

        wp_send_json_success();
    }

    public function handle_save_blank_question() {
        if (!check_ajax_referer('map_drawing_admin_nonce', 'nonce', false)) {
            wp_send_json_error(__('Invalid security token. Please refresh the page and try again.', 'map-drawing-assessment'));
        }

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'map-drawing-assessment'));
        }

        $questions = new Map_Drawing_Assessment_Questions();
        $result = $questions->save_blank_question($_POST);

        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }

        wp_send_json_success();
    }

    public function handle_bulk_action() {
        if (!check_ajax_referer('map_drawing_admin_nonce', 'nonce', false)) {
            wp_send_json_error(__('Invalid security token. Please refresh the page and try again.', 'map-drawing-assessment'));
        }

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'map-drawing-assessment'));
        }

        $questions = new Map_Drawing_Assessment_Questions();
        $result = $questions->handle_bulk_action($_POST['bulk_action'], $_POST['question_ids']);

        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }

        wp_send_json_success();
    }

    public function handle_grant_user_access() {
        if (!check_ajax_referer('map_drawing_admin_nonce', 'nonce', false)) {
            wp_send_json_error(__('Invalid security token. Please refresh the page and try again.', 'map-drawing-assessment'));
        }

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'map-drawing-assessment'));
        }

        $user_id = intval($_POST['user_id']);
        $access_period = intval($_POST['access_period']);

        update_user_meta($user_id, 'map_assessment_access', 'active');
        update_user_meta($user_id, 'map_assessment_access_period', $access_period);
        update_user_meta($user_id, 'map_assessment_access_expiry', date('Y-m-d H:i:s', strtotime("+{$access_period} days")));

        wp_send_json_success();
    }

    public function handle_revoke_user_access() {
        if (!check_ajax_referer('map_drawing_admin_nonce', 'nonce', false)) {
            wp_send_json_error(__('Invalid security token. Please refresh the page and try again.', 'map-drawing-assessment'));
        }

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'map-drawing-assessment'));
        }

        $user_id = intval($_POST['user_id']);
        
        delete_user_meta($user_id, 'map_assessment_access');
        delete_user_meta($user_id, 'map_assessment_access_period');
        delete_user_meta($user_id, 'map_assessment_access_expiry');

        wp_send_json_success();
    }

    /**
     * Register the admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __('Map Assessment', 'map-drawing-assessment'),
            __('Map Assessment', 'map-drawing-assessment'),
            'manage_options',
            $this->plugin_name,
            array($this, 'display_plugin_admin_page'),
            'dashicons-location-alt',
            30
        );
    }

    /**
     * Display the admin page
     */
    public function display_plugin_admin_page() {
        include_once plugin_dir_path(dirname(__FILE__)) . 'templates/admin-dashboard.php';
    }
}