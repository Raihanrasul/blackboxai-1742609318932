<?php
/**
 * The public-facing functionality of the plugin
 */
class Map_Drawing_Assessment_Public {

    private $plugin_name;
    private $version;

    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the public-facing side
     */
    public function enqueue_styles() {
        wp_enqueue_style('leaflet', 'https://unpkg.com/leaflet@1.7.1/dist/leaflet.css', array(), '1.7.1', 'all');
        wp_enqueue_style('fontawesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css', array(), '6.0.0', 'all');
        wp_enqueue_style($this->plugin_name, plugin_dir_url(dirname(__FILE__)) . 'assets/css/public.css', array(), $this->version, 'all');
    }

    /**
     * Register the JavaScript for the public-facing side
     */
    public function enqueue_scripts() {
        wp_enqueue_script('leaflet', 'https://unpkg.com/leaflet@1.7.1/dist/leaflet.js', array('jquery'), '1.7.1', false);
        wp_enqueue_script($this->plugin_name, plugin_dir_url(dirname(__FILE__)) . 'assets/js/public.js', array('jquery', 'leaflet'), $this->version, false);

        wp_localize_script($this->plugin_name, 'mapDrawingPublic', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('map_drawing_public_nonce'),
            'customMapUrl' => MAP_DRAWING_CUSTOM_MAP_URL,
            'isLoggedIn' => is_user_logged_in(),
            'messages' => array(
                'accessDenied' => __('Access denied. Please contact administrator.', 'map-drawing-assessment'),
                'saveSuccess' => __('Progress saved successfully.', 'map-drawing-assessment'),
                'saveError' => __('Error saving progress. Please try again.', 'map-drawing-assessment'),
                'submitSuccess' => __('Assessment submitted successfully.', 'map-drawing-assessment'),
                'submitError' => __('Error submitting assessment. Please try again.', 'map-drawing-assessment')
            )
        ));
    }

    /**
     * Register shortcodes
     */
    public function register_shortcodes() {
        add_shortcode('map_drawing_assessment', array($this, 'render_map_assessment'));
    }

    /**
     * Render map assessment shortcode
     */
    public function render_map_assessment($atts) {
        // Check user access
        if (!$this->check_user_access()) {
            return $this->render_access_denied();
        }

        // Get assessment questions
        $questions = new Map_Drawing_Assessment_Questions();
        $assessment_questions = $questions->get_assessment_questions();

        if (empty($assessment_questions)) {
            return '<p>' . __('No questions available.', 'map-drawing-assessment') . '</p>';
        }

        ob_start();
        include plugin_dir_path(dirname(__FILE__)) . 'templates/public-map.php';
        return ob_get_clean();
    }

    /**
     * Check if user has access to the assessment
     */
    private function check_user_access() {
        if (!is_user_logged_in()) {
            return false;
        }

        $user_id = get_current_user_id();
        $access_status = get_user_meta($user_id, 'map_assessment_access', true);

        if (empty($access_status) || $access_status !== 'active') {
            return false;
        }

        $access_expiry = get_user_meta($user_id, 'map_assessment_access_expiry', true);
        if (!empty($access_expiry) && current_time('timestamp') > strtotime($access_expiry)) {
            return false;
        }

        return true;
    }

    /**
     * Render access denied message
     */
    private function render_access_denied() {
        ob_start();
        ?>
        <div class="map-drawing-access-denied">
            <i class="fas fa-lock"></i>
            <h2><?php _e('Access Denied', 'map-drawing-assessment'); ?></h2>
            <p><?php _e('You do not have access to this assessment. Please contact the administrator.', 'map-drawing-assessment'); ?></p>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Handle saving drawing progress
     */
    public function handle_save_drawing() {
        check_ajax_referer('map_drawing_public_nonce', 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error('User not logged in');
        }

        $drawing_data = array(
            'user_id' => get_current_user_id(),
            'question_id' => intval($_POST['question_id']),
            'drawing' => $_POST['drawing'],
            'is_flagged' => isset($_POST['is_flagged']) ? boolval($_POST['is_flagged']) : false
        );

        $submissions = new Map_Drawing_Assessment_Submissions();
        $result = $submissions->save_drawing_progress($drawing_data);

        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }

        wp_send_json_success('Drawing progress saved');
    }

    /**
     * Handle submitting assessment
     */
    public function handle_submit_assessment() {
        check_ajax_referer('map_drawing_public_nonce', 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error('User not logged in');
        }

        $submission_data = array(
            'user_id' => get_current_user_id(),
            'questions' => $_POST['questions'],
            'answers' => $_POST['answers'],
            'time_taken' => intval($_POST['time_taken'])
        );

        $submissions = new Map_Drawing_Assessment_Submissions();
        $result = $submissions->submit_assessment($submission_data);

        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }

        wp_send_json_success('Assessment submitted successfully');
    }

    /**
     * Handle validating access code
     */
    public function handle_validate_access_code() {
        check_ajax_referer('map_drawing_public_nonce', 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error('User not logged in');
        }

        $access_code = sanitize_text_field($_POST['access_code']);
        $user_id = get_current_user_id();

        // Verify access code from user meta
        $stored_code = get_user_meta($user_id, 'map_assessment_access_code', true);
        if (empty($stored_code) || $stored_code !== $access_code) {
            wp_send_json_error('Invalid access code');
        }

        // Check if code is expired
        $code_expiry = get_user_meta($user_id, 'map_assessment_access_code_expiry', true);
        if (!empty($code_expiry) && current_time('timestamp') > strtotime($code_expiry)) {
            wp_send_json_error('Access code has expired');
        }

        // Update user access status
        update_user_meta($user_id, 'map_assessment_access', 'active');
        delete_user_meta($user_id, 'map_assessment_access_code');
        delete_user_meta($user_id, 'map_assessment_access_code_expiry');

        wp_send_json_success('Access granted');
    }
}