<?php
// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}
?>
<div class="wrap">
    <h1 class="wp-heading-inline"><?php _e('Map Drawing Assessment', 'map-drawing-assessment'); ?></h1>
    <hr class="wp-header-end">

    <div class="dashboard-wrapper">
        <!-- Quick Stats -->
        <div class="postbox">
            <h2 class="hndle"><span><?php _e('Quick Stats', 'map-drawing-assessment'); ?></span></h2>
            <div class="inside">
                <?php
                global $wpdb;
                $stats = array(
                    'total_questions' => $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}map_drawing_questions"),
                    'total_submissions' => $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}map_drawing_submissions"),
                    'pending_corrections' => $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}map_drawing_submissions WHERE status = 'submitted'"),
                    'active_users' => $wpdb->get_var("SELECT COUNT(DISTINCT user_id) FROM {$wpdb->usermeta} WHERE meta_key = 'map_assessment_access' AND meta_value = 'active'")
                );
                ?>
                <div class="stats-grid">
                    <div class="stat-box">
                        <i class="fas fa-question-circle"></i>
                        <span class="stat-number"><?php echo esc_html($stats['total_questions']); ?></span>
                        <span class="stat-label"><?php _e('Total Questions', 'map-drawing-assessment'); ?></span>
                    </div>
                    <div class="stat-box">
                        <i class="fas fa-file-alt"></i>
                        <span class="stat-number"><?php echo esc_html($stats['total_submissions']); ?></span>
                        <span class="stat-label"><?php _e('Total Submissions', 'map-drawing-assessment'); ?></span>
                    </div>
                    <div class="stat-box">
                        <i class="fas fa-clock"></i>
                        <span class="stat-number"><?php echo esc_html($stats['pending_corrections']); ?></span>
                        <span class="stat-label"><?php _e('Pending Corrections', 'map-drawing-assessment'); ?></span>
                    </div>
                    <div class="stat-box">
                        <i class="fas fa-users"></i>
                        <span class="stat-number"><?php echo esc_html($stats['active_users']); ?></span>
                        <span class="stat-label"><?php _e('Active Users', 'map-drawing-assessment'); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="postbox">
            <h2 class="hndle"><span><?php _e('Quick Actions', 'map-drawing-assessment'); ?></span></h2>
            <div class="inside">
                <div class="quick-actions-grid">
                    <a href="<?php echo admin_url('admin.php?page=map-drawing-assessment-map-question'); ?>" class="quick-action-box">
                        <i class="fas fa-map-marker-alt"></i>
                        <span><?php _e('Create Map Question', 'map-drawing-assessment'); ?></span>
                    </a>
                    <a href="<?php echo admin_url('admin.php?page=map-drawing-assessment-mcq'); ?>" class="quick-action-box">
                        <i class="fas fa-tasks"></i>
                        <span><?php _e('Create MCQ', 'map-drawing-assessment'); ?></span>
                    </a>
                    <a href="<?php echo admin_url('admin.php?page=map-drawing-assessment-fill-blanks'); ?>" class="quick-action-box">
                        <i class="fas fa-edit"></i>
                        <span><?php _e('Create Fill Blanks', 'map-drawing-assessment'); ?></span>
                    </a>
                    <a href="<?php echo admin_url('admin.php?page=map-drawing-assessment-submissions'); ?>" class="quick-action-box">
                        <i class="fas fa-inbox"></i>
                        <span><?php _e('View Submissions', 'map-drawing-assessment'); ?></span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="postbox">
            <h2 class="hndle"><span><?php _e('Recent Activity', 'map-drawing-assessment'); ?></span></h2>
            <div class="inside">
                <?php
                $recent_submissions = $wpdb->get_results("
                    SELECT s.*, u.display_name 
                    FROM {$wpdb->prefix}map_drawing_submissions s
                    LEFT JOIN {$wpdb->users} u ON s.user_id = u.ID
                    ORDER BY s.created_at DESC
                    LIMIT 5
                ");

                if ($recent_submissions): ?>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th><?php _e('User', 'map-drawing-assessment'); ?></th>
                                <th><?php _e('Status', 'map-drawing-assessment'); ?></th>
                                <th><?php _e('Marks', 'map-drawing-assessment'); ?></th>
                                <th><?php _e('Submitted', 'map-drawing-assessment'); ?></th>
                                <th><?php _e('Actions', 'map-drawing-assessment'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_submissions as $submission): ?>
                                <tr>
                                    <td><?php echo esc_html($submission->display_name); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo esc_attr($submission->status); ?>">
                                            <?php echo esc_html(ucfirst($submission->status)); ?>
                                        </span>
                                    </td>
                                    <td><?php echo isset($submission->marks) ? esc_html($submission->marks) : '-'; ?></td>
                                    <td><?php echo esc_html(human_time_diff(strtotime($submission->created_at), current_time('timestamp'))); ?> ago</td>
                                    <td>
                                        <a href="<?php echo admin_url('admin.php?page=map-drawing-assessment-submissions&action=view&id=' . $submission->id); ?>" class="button button-small">
                                            <i class="fas fa-eye"></i> <?php _e('View', 'map-drawing-assessment'); ?>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p><?php _e('No recent submissions found.', 'map-drawing-assessment'); ?></p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Plugin Information -->
        <div class="postbox">
            <h2 class="hndle"><span><?php _e('Plugin Information', 'map-drawing-assessment'); ?></span></h2>
            <div class="inside">
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Version', 'map-drawing-assessment'); ?></th>
                        <td><?php echo MAP_DRAWING_ASSESSMENT_VERSION; ?></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Support', 'map-drawing-assessment'); ?></th>
                        <td><a href="https://theseru.co.uk/support" target="_blank"><?php _e('Visit Support Page', 'map-drawing-assessment'); ?></a></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Documentation', 'map-drawing-assessment'); ?></th>
                        <td><a href="https://theseru.co.uk/docs" target="_blank"><?php _e('View Documentation', 'map-drawing-assessment'); ?></a></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
.dashboard-wrapper {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 15px;
}

.stat-box {
    text-align: center;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
}

.stat-box i {
    font-size: 24px;
    color: #007cba;
    margin-bottom: 10px;
}

.stat-number {
    display: block;
    font-size: 24px;
    font-weight: bold;
    color: #1e1e1e;
}

.stat-label {
    display: block;
    font-size: 13px;
    color: #646970;
}

.quick-actions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 15px;
}

.quick-action-box {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
    text-decoration: none;
    color: #1e1e1e;
    transition: all 0.3s ease;
}

.quick-action-box:hover {
    background: #007cba;
    color: #fff;
}

.quick-action-box i {
    font-size: 24px;
    margin-bottom: 10px;
}

.status-badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
}

.status-submitted {
    background: #fef3c7;
    color: #92400e;
}

.status-corrected {
    background: #dcfce7;
    color: #166534;
}

.postbox {
    margin-bottom: 20px;
}

.hndle {
    padding: 12px 15px;
    border-bottom: 1px solid #e2e4e7;
}

.inside {
    padding: 15px;
    margin: 0 !important;
}
</style>