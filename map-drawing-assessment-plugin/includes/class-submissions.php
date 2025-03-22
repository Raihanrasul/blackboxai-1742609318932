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

        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->progress_table} 
            WHERE user_id = %d AND question_id = %d",
            $data['user_id'],
            $data['question_id']
        ));

        $save_data = array(
            'user_id' => $data['user_id'],
            'question_id' => $data['question_id'],
            'drawing' => wp_json_encode($data['drawing']),
            'is_flagged' => !empty($data['is_flagged']),
            'updated_at' => current_time('mysql')
        );

        $format = array('%d', '%d', '%s', '%d', '%s');

        if ($existing) {
            $result = $wpdb->update(
                $this->progress_table,
                $save_data,
                array('id' => $existing->id),
                $format,
                array('%d')
            );
        } else {
            $save_data['created_at'] = current_time('mysql');
            $format[] = '%s';
            $result = $wpdb->insert($this->progress_table, $save_data, $format);
        }

        if ($result === false) {
            return new WP_Error('db_error', 'Failed to save progress');
        }

        return true;
    }

    /**
     * Submit assessment
     */
    public function submit_assessment($data) {
        global $wpdb;

        if (empty($data['user_id'])) {
            return new WP_Error('invalid_data', 'User ID is required');
        }

        // Start transaction
        $wpdb->query('START TRANSACTION');

        try {
            // Save submission
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

            // Update progress records with submission ID
            $wpdb->query($wpdb->prepare(
                "UPDATE {$this->progress_table} 
                SET submission_id = %d 
                WHERE user_id = %d AND submission_id IS NULL",
                $submission_id,
                $data['user_id']
            ));

            $wpdb->query('COMMIT');
            return $submission_id;

        } catch (Exception $e) {
            $wpdb->query('ROLLBACK');
            return new WP_Error('submission_error', $e->getMessage());
        }
    }

    /**
     * Get submission details
     */
    public function get_submission($id) {
        global $wpdb;

        $submission = $wpdb->get_row($wpdb->prepare(
            "SELECT s.*, u.display_name as user_name 
            FROM {$this->submissions_table} s 
            LEFT JOIN {$wpdb->users} u ON s.user_id = u.ID 
            WHERE s.id = %d",
            $id
        ));

        if (!$submission) {
            return null;
        }

        // Get progress records
        $progress = $wpdb->get_results($wpdb->prepare(
            "SELECT p.*, q.type, q.title, q.content 
            FROM {$this->progress_table} p 
            LEFT JOIN {$wpdb->prefix}map_drawing_questions q ON p.question_id = q.id 
            WHERE p.submission_id = %d",
            $id
        ));

        if ($progress) {
            foreach ($progress as &$item) {
                $item->drawing = json_decode($item->drawing, true);
                $item->content = json_decode($item->content, true);
            }
            $submission->questions = $progress;
        }

        return $submission;
    }

    /**
     * Save correction
     */
    public function save_correction($data) {
        global $wpdb;

        if (empty($data['submission_id'])) {
            return new WP_Error('invalid_data', 'Submission ID is required');
        }

        // Update submission status
        $result = $wpdb->update(
            $this->submissions_table,
            array(
                'status' => 'corrected',
                'updated_at' => current_time('mysql')
            ),
            array('id' => $data['submission_id']),
            array('%s', '%s'),
            array('%d')
        );

        if ($result === false) {
            return new WP_Error('db_error', 'Failed to update submission');
        }

        // Update progress records with marks and comments
        foreach ($data['marks'] as $question_id => $marks) {
            $wpdb->update(
                $this->progress_table,
                array(
                    'marks' => floatval($marks),
                    'comments' => sanitize_textarea_field($data['comments'][$question_id]),
                    'corrected_route' => isset($data['corrected_routes'][$question_id]) ? 
                        wp_json_encode($data['corrected_routes'][$question_id]) : null
                ),
                array(
                    'submission_id' => $data['submission_id'],
                    'question_id' => $question_id
                ),
                array('%f', '%s', '%s'),
                array('%d', '%d')
            );
        }

        return true;
    }

    /**
     * Create required tables
     */
    public static function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        // Submissions table
        $submissions_table = $wpdb->prefix . 'map_drawing_submissions';
        $sql = "CREATE TABLE IF NOT EXISTS $submissions_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            time_taken int(11) NOT NULL,
            status varchar(20) NOT NULL,
            created_at datetime NOT NULL,
            updated_at datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY user_id (user_id)
        ) $charset_collate;";

        // Progress table
        $progress_table = $wpdb->prefix . 'map_drawing_progress';
        $sql .= "CREATE TABLE IF NOT EXISTS $progress_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            submission_id bigint(20) DEFAULT NULL,
            user_id bigint(20) NOT NULL,
            question_id bigint(20) NOT NULL,
            drawing longtext NOT NULL,
            corrected_route longtext DEFAULT NULL,
            marks float DEFAULT NULL,
            comments text DEFAULT NULL,
            is_flagged tinyint(1) NOT NULL DEFAULT 0,
            created_at datetime NOT NULL,
            updated_at datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY submission_id (submission_id),
            KEY user_id (user_id),
            KEY question_id (question_id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}