<?php
/**
 * Handles all submission-related operations
 */
class Map_Drawing_Assessment_Submissions {

    private $submissions_table;
    private $progress_table;

    public function __construct() {
        global $wpdb;
        $this->submissions_table = $wpdb->prefix . 'map_drawing_submissions';
        $this->progress_table = $wpdb->prefix . 'map_drawing_progress';
    }

    /**
     * Save drawing progress
     */
    public function save_drawing_progress($data) {
        global $wpdb;

        if (empty($data['user_id']) || empty($data['question_id'])) {
            return new WP_Error('invalid_data', 'User ID and Question ID are required');
        }

        $progress_data = array(
            'user_id' => $data['user_id'],
            'question_id' => $data['question_id'],
            'drawing_data' => wp_json_encode($data['drawing']),
            'is_flagged' => !empty($data['is_flagged']),
            'updated_at' => current_time('mysql')
        );

        // Check if progress exists
        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT id FROM {$this->progress_table} WHERE user_id = %d AND question_id = %d",
            $data['user_id'],
            $data['question_id']
        ));

        if ($existing) {
            $result = $wpdb->update(
                $this->progress_table,
                $progress_data,
                array('id' => $existing->id),
                array('%d', '%d', '%s', '%d', '%s'),
                array('%d')
            );
        } else {
            $progress_data['created_at'] = current_time('mysql');
            $result = $wpdb->insert(
                $this->progress_table,
                $progress_data,
                array('%d', '%d', '%s', '%d', '%s', '%s')
            );
        }

        if ($result === false) {
            return new WP_Error('db_error', 'Failed to save progress');
        }

        return true;
    }

    /**
     * Submit complete assessment
     */
    public function submit_assessment($data) {
        global $wpdb;

        if (empty($data['user_id']) || empty($data['questions'])) {
            return new WP_Error('invalid_data', 'User ID and questions are required');
        }

        // Start transaction
        $wpdb->query('START TRANSACTION');

        try {
            // Save submission record
            $submission_data = array(
                'user_id' => $data['user_id'],
                'time_taken' => $data['time_taken'],
                'status' => 'submitted',
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql')
            );

            $result = $wpdb->insert(
                $this->submissions_table,
                $submission_data,
                array('%d', '%d', '%s', '%s', '%s')
            );

            if ($result === false) {
                throw new Exception('Failed to save submission');
            }

            $submission_id = $wpdb->insert_id;

            // Save answers for each question
            foreach ($data['questions'] as $index => $question) {
                $answer_data = array(
                    'submission_id' => $submission_id,
                    'question_id' => $question['id'],
                    'answer_data' => wp_json_encode($data['answers'][$index]),
                    'created_at' => current_time('mysql')
                );

                $result = $wpdb->insert(
                    $wpdb->prefix . 'map_drawing_answers',
                    $answer_data,
                    array('%d', '%d', '%s', '%s')
                );

                if ($result === false) {
                    throw new Exception('Failed to save answers');
                }
            }

            // Clear progress data
            $wpdb->delete(
                $this->progress_table,
                array('user_id' => $data['user_id']),
                array('%d')
            );

            $wpdb->query('COMMIT');
            return $submission_id;

        } catch (Exception $e) {
            $wpdb->query('ROLLBACK');
            return new WP_Error('submission_error', $e->getMessage());
        }
    }

    /**
     * Save correction for a submission
     */
    public function save_correction($data) {
        global $wpdb;

        if (empty($data['submission_id'])) {
            return new WP_Error('invalid_data', 'Submission ID is required');
        }

        $correction_data = array(
            'marks' => $data['marks'],
            'comments' => $data['comments'],
            'corrected_route' => wp_json_encode($data['corrected_route']),
            'status' => 'corrected',
            'updated_at' => current_time('mysql')
        );

        $result = $wpdb->update(
            $this->submissions_table,
            $correction_data,
            array('id' => $data['submission_id']),
            array('%f', '%s', '%s', '%s', '%s'),
            array('%d')
        );

        if ($result === false) {
            return new WP_Error('db_error', 'Failed to save correction');
        }

        return true;
    }

    /**
     * Get submission details
     */
    public function get_submission($id) {
        global $wpdb;

        $submission = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->submissions_table} WHERE id = %d",
            $id
        ));

        if (!$submission) {
            return null;
        }

        // Get answers
        $answers = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}map_drawing_answers WHERE submission_id = %d",
            $id
        ));

        if ($answers) {
            foreach ($answers as $answer) {
                $answer->answer_data = json_decode($answer->answer_data, true);
            }
            $submission->answers = $answers;
        }

        return $submission;
    }

    /**
     * Get user's submissions
     */
    public function get_user_submissions($user_id) {
        global $wpdb;

        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$this->submissions_table} WHERE user_id = %d ORDER BY created_at DESC",
            $user_id
        ));
    }

    /**
     * Get all submissions with optional filters
     */
    public function get_submissions($filters = array()) {
        global $wpdb;

        $sql = "SELECT s.*, u.display_name as user_name 
                FROM {$this->submissions_table} s 
                LEFT JOIN {$wpdb->users} u ON s.user_id = u.ID 
                WHERE 1=1";

        if (!empty($filters['user_id'])) {
            $sql .= $wpdb->prepare(" AND s.user_id = %d", $filters['user_id']);
        }

        if (!empty($filters['status'])) {
            $sql .= $wpdb->prepare(" AND s.status = %s", $filters['status']);
        }

        $sql .= " ORDER BY s.created_at DESC";

        return $wpdb->get_results($sql);
    }

    /**
     * Create submissions related tables
     */
    public static function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        // Submissions table
        $submissions_table = $wpdb->prefix . 'map_drawing_submissions';
        $sql = "CREATE TABLE IF NOT EXISTS $submissions_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            time_taken int(11) NOT NULL,
            marks float DEFAULT NULL,
            comments text DEFAULT NULL,
            corrected_route longtext DEFAULT NULL,
            status varchar(20) NOT NULL DEFAULT 'submitted',
            created_at datetime NOT NULL,
            updated_at datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY user_id (user_id)
        ) $charset_collate;";
        dbDelta($sql);

        // Progress table
        $progress_table = $wpdb->prefix . 'map_drawing_progress';
        $sql = "CREATE TABLE IF NOT EXISTS $progress_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            question_id bigint(20) NOT NULL,
            drawing_data longtext NOT NULL,
            is_flagged tinyint(1) NOT NULL DEFAULT 0,
            created_at datetime NOT NULL,
            updated_at datetime NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY user_question (user_id,question_id)
        ) $charset_collate;";
        dbDelta($sql);

        // Answers table
        $answers_table = $wpdb->prefix . 'map_drawing_answers';
        $sql = "CREATE TABLE IF NOT EXISTS $answers_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            submission_id bigint(20) NOT NULL,
            question_id bigint(20) NOT NULL,
            answer_data longtext NOT NULL,
            created_at datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY submission_id (submission_id)
        ) $charset_collate;";
        dbDelta($sql);
    }
}