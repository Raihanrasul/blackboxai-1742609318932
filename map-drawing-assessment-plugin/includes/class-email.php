<?php
/**
 * Handles all email-related functionality
 */
class Map_Drawing_Assessment_Email {

    /**
     * Send access code email
     */
    public function send_access_code_email($user_id, $access_period) {
        $user = get_user_by('id', $user_id);
        if (!$user) {
            return new WP_Error('invalid_user', 'User not found');
        }

        // Generate access code
        $access_code = $this->generate_access_code();
        $expiry_days = get_option('map_drawing_assessment_access_token_expiry_days', 2);

        // Save access code to user meta
        update_user_meta($user_id, 'map_assessment_access_code', $access_code);
        update_user_meta($user_id, 'map_assessment_access_code_expiry', 
            date('Y-m-d H:i:s', strtotime("+{$expiry_days} days")));
        update_user_meta($user_id, 'map_assessment_access_period', $access_period);

        // Get email template
        $subject = get_option('map_drawing_assessment_access_code_subject');
        $body = get_option('map_drawing_assessment_access_code_body');

        // Replace placeholders
        $placeholders = array(
            '{access_code}' => $access_code,
            '{expiry_days}' => $expiry_days,
            '{assessment_url}' => home_url('/map-assessment/'),
            '{site_name}' => get_bloginfo('name')
        );

        $body = str_replace(array_keys($placeholders), array_values($placeholders), $body);

        // Send email
        return $this->send_email($user->user_email, $subject, $body);
    }

    /**
     * Send results email
     */
    public function send_result_email($submission_id, $user_id) {
        $user = get_user_by('id', $user_id);
        if (!$user) {
            return new WP_Error('invalid_user', 'User not found');
        }

        // Get submission details
        $submissions = new Map_Drawing_Assessment_Submissions();
        $submission = $submissions->get_submission($submission_id);
        if (!$submission) {
            return new WP_Error('invalid_submission', 'Submission not found');
        }

        // Generate temporary access token
        $access_token = wp_generate_password(32, false);
        $expiry_days = get_option('map_drawing_assessment_access_token_expiry_days', 2);

        set_transient(
            'map_assessment_result_' . $access_token, 
            $submission_id, 
            $expiry_days * DAY_IN_SECONDS
        );

        // Get email template
        $subject = get_option('map_drawing_assessment_results_subject');
        $body = get_option('map_drawing_assessment_results_body');

        // Calculate total score
        $total_score = 0;
        $max_score = 0;
        foreach ($submission->questions as $question) {
            $total_score += floatval($question->marks);
            $max_score += floatval($question->max_marks);
        }
        $score_percentage = $max_score > 0 ? round(($total_score / $max_score) * 100, 1) : 0;

        // Format time taken
        $minutes = floor($submission->time_taken / 60);
        $seconds = $submission->time_taken % 60;
        $time_taken = sprintf('%d min %d sec', $minutes, $seconds);

        // Replace placeholders
        $placeholders = array(
            '{total_score}' => $score_percentage . '%',
            '{time_taken}' => $time_taken,
            '{results_url}' => add_query_arg(
                array('token' => $access_token),
                home_url('/map-assessment/results/')
            ),
            '{expiry_days}' => $expiry_days,
            '{site_name}' => get_bloginfo('name')
        );

        $body = str_replace(array_keys($placeholders), array_values($placeholders), $body);

        // Send email
        return $this->send_email($user->user_email, $subject, $body);
    }

    /**
     * Generate access code
     */
    private function generate_access_code() {
        $length = get_option('map_drawing_assessment_access_code_length', 8);
        $chars = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ'; // Excluding similar looking characters
        $code = '';
        
        for ($i = 0; $i < $length; $i++) {
            $code .= $chars[rand(0, strlen($chars) - 1)];
        }
        
        return $code;
    }

    /**
     * Send email
     */
    private function send_email($to, $subject, $body) {
        $headers = array(
            'Content-Type: text/plain; charset=UTF-8',
            'From: ' . get_option('map_drawing_assessment_email_from_name') . 
                ' <' . get_option('map_drawing_assessment_email_from_address') . '>'
        );

        $sent = wp_mail($to, $subject, $body, $headers);

        if (!$sent) {
            return new WP_Error('email_error', 'Failed to send email');
        }

        return true;
    }
}