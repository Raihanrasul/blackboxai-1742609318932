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
    }

    /**
     * Register the stylesheets for the admin area
     */
    public function enqueue_styles() {
        wp_enqueue_style('leaflet', 'https://unpkg.com/leaflet@1.7.1/dist/leaflet.css', array(), '1.7.1', 'all');
        wp_enqueue_style($this->plugin_name, plugin_dir_url(dirname(__FILE__)) . 'assets/css/admin.css', array(), $this->version, 'all');
    }

    /**
     * Register the JavaScript for the admin area
     */
    public function enqueue_scripts() {
        wp_enqueue_script('leaflet', 'https://unpkg.com/leaflet@1.7.1/dist/leaflet.js', array('jquery'), '1.7.1', false);
        wp_enqueue_script($this->plugin_name, plugin_dir_url(dirname(__FILE__)) . 'assets/js/admin.js', array('jquery', 'leaflet'), $this->version, false);
        
        wp_localize_script($this->plugin_name, 'mapDrawingAdmin', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('map_drawing_admin_nonce'),
            'customMapUrl' => MAP_DRAWING_CUSTOM_MAP_URL
        ));
    }

    /**
     * Add plugin admin menu
     */
    public function add_plugin_admin_menu() {
        add_menu_page(
            'Map Drawing Assessment',
            'Map Assessment',
            'manage_options',
            $this->plugin_name,
            array($this, 'display_plugin_admin_dashboard'),
            'dashicons-location-alt',
            30
        );

        add_submenu_page(
            $this->plugin_name,
            'Create Map Question',
            'Create Map Question',
            'manage_options',
            $this->plugin_name . '-map-question',
            array($this, 'display_map_question_page')
        );

        add_submenu_page(
            $this->plugin_name,
            'Create MCQ',
            'Create MCQ',
            'manage_options',
            $this->plugin_name . '-mcq',
            array($this, 'display_mcq_page')
        );

        add_submenu_page(
            $this->plugin_name,
            'Create Fill Blanks',
            'Create Fill Blanks',
            'manage_options',
            $this->plugin_name . '-fill-blanks',
            array($this, 'display_fill_blanks_page')
        );

        add_submenu_page(
            $this->plugin_name,
            'Manage Questions',
            'Manage Questions',
            'manage_options',
            $this->plugin_name . '-manage',
            array($this, 'display_manage_questions_page')
        );

        add_submenu_page(
            $this->plugin_name,
            'View Submissions',
            'View Submissions',
            'manage_options',
            $this->plugin_name . '-submissions',
            array($this, 'display_submissions_page')
        );

        add_submenu_page(
            $this->plugin_name,
            'User Management',
            'User Management',
            'manage_options',
            $this->plugin_name . '-users',
            array($this, 'display_user_management_page')
        );
    }

    /**
     * Display admin dashboard
     */
    public function display_plugin_admin_dashboard() {
        require_once plugin_dir_path(dirname(__FILE__)) . 'templates/admin-dashboard.php';
    }

    /**
     * Display map question creation page
     */
    public function display_map_question_page() {
        require_once plugin_dir_path(dirname(__FILE__)) . 'templates/admin-map-question.php';
    }

    /**
     * Display MCQ creation page
     */
    public function display_mcq_page() {
        require_once plugin_dir_path(dirname(__FILE__)) . 'templates/admin-mcq.php';
    }

    /**
     * Display fill in the blanks creation page
     */
    public function display_fill_blanks_page() {
        require_once plugin_dir_path(dirname(__FILE__)) . 'templates/admin-fill-blanks.php';
    }

    /**
     * Display question management page
     */
    public function display_manage_questions_page() {
        require_once plugin_dir_path(dirname(__FILE__)) . 'templates/admin-manage-questions.php';
    }

    /**
     * Display submissions page
     */
    public function display_submissions_page() {
        require_once plugin_dir_path(dirname(__FILE__)) . 'templates/admin-submissions.php';
    }

    /**
     * Display user management page
     */
    public function display_user_management_page() {
        require_once plugin_dir_path(dirname(__FILE__)) . 'templates/admin-user-management.php';
    }

    /**
     * Handle saving map question
     */
    public function handle_save_map_question() {
        check_ajax_referer('map_drawing_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized access');
        }

        $question_data = array(
            'type' => 'map',
            'title' => sanitize_text_field($_POST['title']),
            'instructions' => wp_kses_post($_POST['instructions']),
            'start_marker' => array(
                'lat' => floatval($_POST['start_lat']),
                'lng' => floatval($_POST['start_lng'])
            ),
            'end_marker' => array(
                'lat' => floatval($_POST['end_lat']),
                'lng' => floatval($_POST['end_lng'])
            )
        );

        $questions = new Map_Drawing_Assessment_Questions();
        $result = $questions->save_question($question_data);

        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }

        wp_send_json_success('Question saved successfully');
    }

    /**
     * Handle saving MCQ question
     */
    public function handle_save_mcq_question() {
        check_ajax_referer('map_drawing_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized access');
        }

        $question_data = array(
            'type' => 'mcq',
            'title' => sanitize_text_field($_POST['title']),
            'question' => wp_kses_post($_POST['question']),
            'options' => array_map('sanitize_text_field', $_POST['options']),
            'correct_answers' => array_map('intval', $_POST['correct_answers']),
            'points' => intval($_POST['points'])
        );

        $questions = new Map_Drawing_Assessment_Questions();
        $result = $questions->save_question($question_data);

        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }

        wp_send_json_success('Question saved successfully');
    }

    /**
     * Handle saving fill in the blanks question
     */
    public function handle_save_blank_question() {
        check_ajax_referer('map_drawing_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized access');
        }

        $question_data = array(
            'type' => 'blanks',
            'title' => sanitize_text_field($_POST['title']),
            'sentence' => wp_kses_post($_POST['sentence']),
            'options' => array_map('sanitize_text_field', $_POST['options']),
            'correct_answers' => array_map('sanitize_text_field', $_POST['correct_answers'])
        );

        $questions = new Map_Drawing_Assessment_Questions();
        $result = $questions->save_question($question_data);

        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }

        wp_send_json_success('Question saved successfully');
    }

    /**
     * Handle bulk actions for questions
     */
    public function handle_bulk_action() {
        check_ajax_referer('map_drawing_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized access');
        }

        $action = sanitize_text_field($_POST['bulk_action']);
        $question_ids = array_map('intval', $_POST['question_ids']);

        $questions = new Map_Drawing_Assessment_Questions();
        $result = $questions->handle_bulk_action($action, $question_ids);

        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }

        wp_send_json_success('Bulk action completed successfully');
    }

    /**
     * Handle saving correction
     */
    public function handle_save_correction() {
        check_ajax_referer('map_drawing_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized access');
        }

        $correction_data = array(
            'submission_id' => intval($_POST['submission_id']),
            'marks' => floatval($_POST['marks']),
            'comments' => wp_kses_post($_POST['comments']),
            'corrected_route' => $_POST['corrected_route']
        );

        $submissions = new Map_Drawing_Assessment_Submissions();
        $result = $submissions->save_correction($correction_data);

        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }

        wp_send_json_success('Correction saved successfully');
    }

    /**
     * Handle sending result email
     */
    public function handle_send_result_email() {
        check_ajax_referer('map_drawing_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized access');
        }

        $submission_id = intval($_POST['submission_id']);
        $user_id = intval($_POST['user_id']);

        $email = new Map_Drawing_Assessment_Email();
        $result = $email->send_result_email($submission_id, $user_id);

        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }

        wp_send_json_success('Result email sent successfully');
    }
}