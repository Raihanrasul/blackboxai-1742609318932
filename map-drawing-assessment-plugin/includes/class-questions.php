<?php
/**
 * Handles all question-related operations
 */
class Map_Drawing_Assessment_Questions {

    private $table_name;

    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'map_drawing_questions';
    }

    /**
     * Create questions table
     */
    public static function create_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'map_drawing_questions';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            type varchar(20) NOT NULL,
            title varchar(255) NOT NULL,
            content longtext NOT NULL,
            created_at datetime NOT NULL,
            updated_at datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY type (type)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Save a question
     */
    public function save_question($data) {
        global $wpdb;

        if (empty($data['type'])) {
            return new WP_Error('invalid_data', 'Question type is required');
        }

        $save_data = array(
            'type' => sanitize_text_field($data['type']),
            'title' => sanitize_text_field($data['title']),
            'content' => wp_json_encode($this->prepare_question_content($data)),
            'updated_at' => current_time('mysql')
        );

        $format = array('%s', '%s', '%s', '%s');

        if (!empty($data['id'])) {
            $result = $wpdb->update(
                $this->table_name,
                $save_data,
                array('id' => $data['id']),
                $format,
                array('%d')
            );
        } else {
            $save_data['created_at'] = current_time('mysql');
            $format[] = '%s';
            $result = $wpdb->insert($this->table_name, $save_data, $format);
        }

        if ($result === false) {
            return new WP_Error('db_error', 'Failed to save question');
        }

        return !empty($data['id']) ? $data['id'] : $wpdb->insert_id;
    }

    /**
     * Prepare question content based on type
     */
    private function prepare_question_content($data) {
        $content = array();

        switch ($data['type']) {
            case 'map':
                $content = array(
                    'instructions' => wp_kses_post($data['instructions']),
                    'start_marker' => array(
                        'lat' => floatval($data['start_lat']),
                        'lng' => floatval($data['start_lng'])
                    ),
                    'end_marker' => array(
                        'lat' => floatval($data['end_lat']),
                        'lng' => floatval($data['end_lng'])
                    )
                );
                break;

            case 'mcq':
                $content = array(
                    'question' => wp_kses_post($data['question']),
                    'options' => array_map('sanitize_text_field', $data['options']),
                    'correct_answers' => array_map('intval', $data['correct_answers']),
                    'points' => intval($data['points'])
                );
                break;

            case 'blanks':
                $content = array(
                    'sentence' => wp_kses_post($data['sentence']),
                    'options' => array_map('sanitize_text_field', $data['options']),
                    'correct_answers' => array_map('sanitize_text_field', $data['correct_answers'])
                );
                break;
        }

        return $content;
    }

    /**
     * Get questions
     */
    public function get_questions($type = null) {
        global $wpdb;

        $sql = "SELECT * FROM {$this->table_name} WHERE 1=1";
        
        if ($type) {
            $sql .= $wpdb->prepare(" AND type = %s", $type);
        }

        $sql .= " ORDER BY created_at DESC";

        $questions = $wpdb->get_results($sql);

        if (!$questions) {
            return array();
        }

        return array_map(function($question) {
            $question->content = json_decode($question->content, true);
            return $question;
        }, $questions);
    }

    /**
     * Get a single question
     */
    public function get_question($id) {
        global $wpdb;

        $question = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$this->table_name} WHERE id = %d", $id)
        );

        if (!$question) {
            return null;
        }

        $question->content = json_decode($question->content, true);
        return $question;
    }

    /**
     * Delete questions
     */
    public function delete_questions($ids) {
        global $wpdb;

        if (!is_array($ids)) {
            $ids = array($ids);
        }

        $ids = array_map('intval', $ids);
        $placeholders = implode(',', array_fill(0, count($ids), '%d'));

        $result = $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$this->table_name} WHERE id IN ($placeholders)",
                $ids
            )
        );

        if ($result === false) {
            return new WP_Error('db_error', 'Failed to delete questions');
        }

        return $result;
    }

    /**
     * Handle bulk actions
     */
    public function handle_bulk_action($action, $question_ids) {
        switch ($action) {
            case 'delete':
                return $this->delete_questions($question_ids);

            default:
                return new WP_Error('invalid_action', 'Invalid bulk action');
        }
    }

    /**
     * Get questions for assessment
     */
    public function get_assessment_questions() {
        global $wpdb;

        $questions = $wpdb->get_results("
            SELECT id, type, title, content 
            FROM {$this->table_name} 
            ORDER BY RAND()
        ");

        if (!$questions) {
            return array();
        }

        return array_map(function($question) {
            $question->content = json_decode($question->content, true);
            
            // Remove correct answers for MCQ and blanks questions
            if (in_array($question->type, array('mcq', 'blanks'))) {
                unset($question->content['correct_answers']);
            }
            
            return $question;
        }, $questions);
    }
}