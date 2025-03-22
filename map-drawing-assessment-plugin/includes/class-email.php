<?php
/**
 * Handles all email-related functionality
 */
class Map_Drawing_Assessment_Email {

    /**
     * Send result email to user
     */
    public function send_result_email($submission_id, $user_id) {
        $user = get_user_by('id', $user_id);
        if (!$user) {
            return new WP_Error('invalid_user', 'User not found');
        }

        $submissions = new Map_Drawing_Assessment_Submissions();
        $submission = $submissions->get_submission($submission_id);
        if (!$submission) {
            return new WP_Error('invalid_submission', 'Submission not found');
        }

        // Generate temporary access token
        $access_token = $this->generate_access_token();
        $expiry = date('Y-m-d H:i:s', strtotime('+2 days'));

        // Save access token to user meta
        update_user_meta($user_id, 'map_assessment_result_token', $access_token);
        update_user_meta($user_id, 'map_assessment_result_token_expiry', $expiry);
        update_user_meta($user_id, 'map_assessment_result_submission_id', $submission_id);

        // Generate result URL
        $result_url = add_query_arg(array(
            'action' => 'view_assessment_result',
            'token' => $access_token
        ), home_url());

        // Prepare email content
        $subject = sprintf(__('Your Assessment Results - %s', 'map-drawing-assessment'), get_bloginfo('name'));
        $message = $this->get_result_email_content($user, $submission, $result_url, $expiry);
        $headers = array('Content-Type: text/html; charset=UTF-8');

        // Send email
        $sent = wp_mail($user->user_email, $subject, $message, $headers);

        if (!$sent) {
            return new WP_Error('email_error', 'Failed to send email');
        }

        return true;
    }

    /**
     * Send access code email to user
     */
    public function send_access_code_email($user_id, $access_period) {
        $user = get_user_by('id', $user_id);
        if (!$user) {
            return new WP_Error('invalid_user', 'User not found');
        }

        // Generate access code
        $access_code = $this->generate_access_code();
        $expiry = date('Y-m-d H:i:s', strtotime("+{$access_period} days"));

        // Save access code to user meta
        update_user_meta($user_id, 'map_assessment_access_code', $access_code);
        update_user_meta($user_id, 'map_assessment_access_code_expiry', $expiry);
        update_user_meta($user_id, 'map_assessment_access_period', $access_period);

        // Generate assessment URL
        $assessment_url = add_query_arg(array(
            'assessment' => 'map-drawing'
        ), home_url());

        // Prepare email content
        $subject = sprintf(__('Access Code for Map Drawing Assessment - %s', 'map-drawing-assessment'), get_bloginfo('name'));
        $message = $this->get_access_code_email_content($user, $access_code, $assessment_url, $expiry);
        $headers = array('Content-Type: text/html; charset=UTF-8');

        // Send email
        $sent = wp_mail($user->user_email, $subject, $message, $headers);

        if (!$sent) {
            return new WP_Error('email_error', 'Failed to send email');
        }

        return true;
    }

    /**
     * Generate result email content
     */
    private function get_result_email_content($user, $submission, $result_url, $expiry) {
        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #f8f9fa; padding: 20px; text-align: center; }
                .content { padding: 20px; }
                .button { display: inline-block; padding: 10px 20px; background: #007bff; color: #fff; text-decoration: none; border-radius: 5px; }
                .footer { font-size: 12px; color: #666; text-align: center; margin-top: 20px; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h2><?php _e('Your Assessment Results', 'map-drawing-assessment'); ?></h2>
                </div>
                <div class="content">
                    <p><?php printf(__('Hello %s,', 'map-drawing-assessment'), $user->display_name); ?></p>
                    
                    <p><?php _e('Your assessment has been reviewed and your results are now available.', 'map-drawing-assessment'); ?></p>
                    
                    <p><strong><?php _e('Assessment Details:', 'map-drawing-assessment'); ?></strong></p>
                    <ul>
                        <li><?php printf(__('Marks: %s', 'map-drawing-assessment'), $submission->marks); ?></li>
                        <li><?php printf(__('Submission Date: %s', 'map-drawing-assessment'), date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($submission->created_at))); ?></li>
                    </ul>

                    <p><?php _e('To view your detailed results, including corrections and comments, please click the button below:', 'map-drawing-assessment'); ?></p>
                    
                    <p style="text-align: center;">
                        <a href="<?php echo esc_url($result_url); ?>" class="button"><?php _e('View Results', 'map-drawing-assessment'); ?></a>
                    </p>

                    <p><em><?php printf(__('Note: This link will expire on %s.', 'map-drawing-assessment'), date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($expiry))); ?></em></p>
                </div>
                <div class="footer">
                    <p><?php printf(__('This email was sent from %s', 'map-drawing-assessment'), get_bloginfo('name')); ?></p>
                </div>
            </div>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }

    /**
     * Generate access code email content
     */
    private function get_access_code_email_content($user, $access_code, $assessment_url, $expiry) {
        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #f8f9fa; padding: 20px; text-align: center; }
                .content { padding: 20px; }
                .access-code { font-size: 24px; text-align: center; padding: 20px; background: #f8f9fa; margin: 20px 0; letter-spacing: 5px; }
                .button { display: inline-block; padding: 10px 20px; background: #007bff; color: #fff; text-decoration: none; border-radius: 5px; }
                .footer { font-size: 12px; color: #666; text-align: center; margin-top: 20px; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h2><?php _e('Map Drawing Assessment Access', 'map-drawing-assessment'); ?></h2>
                </div>
                <div class="content">
                    <p><?php printf(__('Hello %s,', 'map-drawing-assessment'), $user->display_name); ?></p>
                    
                    <p><?php _e('You have been granted access to the Map Drawing Assessment. Please use the following access code when prompted:', 'map-drawing-assessment'); ?></p>
                    
                    <div class="access-code">
                        <strong><?php echo esc_html($access_code); ?></strong>
                    </div>

                    <p><?php _e('To start your assessment, please click the button below:', 'map-drawing-assessment'); ?></p>
                    
                    <p style="text-align: center;">
                        <a href="<?php echo esc_url($assessment_url); ?>" class="button"><?php _e('Start Assessment', 'map-drawing-assessment'); ?></a>
                    </p>

                    <p><em><?php printf(__('Note: This access code will expire on %s.', 'map-drawing-assessment'), date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($expiry))); ?></em></p>
                </div>
                <div class="footer">
                    <p><?php printf(__('This email was sent from %s', 'map-drawing-assessment'), get_bloginfo('name')); ?></p>
                </div>
            </div>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }

    /**
     * Generate random access token
     */
    private function generate_access_token() {
        return wp_generate_password(32, false);
    }

    /**
     * Generate random access code
     */
    private function generate_access_code() {
        return strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
    }
}