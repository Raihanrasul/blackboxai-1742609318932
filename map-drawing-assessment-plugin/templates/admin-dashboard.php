<?php
// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Get statistics
$questions = new Map_Drawing_Assessment_Questions();
$submissions = new Map_Drawing_Assessment_Submissions();

$total_questions = count($questions->get_questions());
$total_map_questions = count($questions->get_questions('map'));
$total_mcq_questions = count($questions->get_questions('mcq'));
$total_blank_questions = count($questions->get_questions('blanks'));

$recent_submissions = $submissions->get_submissions(array('limit' => 5));
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php _e('Map Assessment Dashboard', 'map-drawing-assessment'); ?></h1>

    <div class="dashboard-stats">
        <div class="stat-box">
            <i class="fas fa-question-circle"></i>
            <div class="stat-content">
                <h3><?php echo $total_questions; ?></h3>
                <p><?php _e('Total Questions', 'map-drawing-assessment'); ?></p>
            </div>
        </div>

        <div class="stat-box">
            <i class="fas fa-map-marker-alt"></i>
            <div class="stat-content">
                <h3><?php echo $total_map_questions; ?></h3>
                <p><?php _e('Map Questions', 'map-drawing-assessment'); ?></p>
            </div>
        </div>

        <div class="stat-box">
            <i class="fas fa-tasks"></i>
            <div class="stat-content">
                <h3><?php echo $total_mcq_questions; ?></h3>
                <p><?php _e('MCQ Questions', 'map-drawing-assessment'); ?></p>
            </div>
        </div>

        <div class="stat-box">
            <i class="fas fa-edit"></i>
            <div class="stat-content">
                <h3><?php echo $total_blank_questions; ?></h3>
                <p><?php _e('Fill in Blanks', 'map-drawing-assessment'); ?></p>
            </div>
        </div>
    </div>

    <div class="dashboard-actions">
        <a href="<?php echo admin_url('admin.php?page=map-drawing-assessment-map-question'); ?>" class="button button-primary">
            <i class="fas fa-plus"></i> <?php _e('Add Map Question', 'map-drawing-assessment'); ?>
        </a>
        <a href="<?php echo admin_url('admin.php?page=map-drawing-assessment-mcq'); ?>" class="button button-primary">
            <i class="fas fa-plus"></i> <?php _e('Add MCQ', 'map-drawing-assessment'); ?>
        </a>
        <a href="<?php echo admin_url('admin.php?page=map-drawing-assessment-fill-blanks'); ?>" class="button button-primary">
            <i class="fas fa-plus"></i> <?php _e('Add Fill Blanks', 'map-drawing-assessment'); ?>
        </a>
        <a href="<?php echo admin_url('admin.php?page=map-drawing-assessment-users'); ?>" class="button">
            <i class="fas fa-users"></i> <?php _e('Manage Users', 'map-drawing-assessment'); ?>
        </a>
    </div>

    <?php if ($recent_submissions): ?>
    <div class="recent-submissions">
        <h2><?php _e('Recent Submissions', 'map-drawing-assessment'); ?></h2>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php _e('User', 'map-drawing-assessment'); ?></th>
                    <th><?php _e('Date', 'map-drawing-assessment'); ?></th>
                    <th><?php _e('Time Taken', 'map-drawing-assessment'); ?></th>
                    <th><?php _e('Status', 'map-drawing-assessment'); ?></th>
                    <th><?php _e('Actions', 'map-drawing-assessment'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent_submissions as $submission): ?>
                    <tr>
                        <td><?php echo esc_html($submission->user_name); ?></td>
                        <td><?php echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($submission->created_at)); ?></td>
                        <td>
                            <?php 
                            $minutes = floor($submission->time_taken / 60);
                            $seconds = $submission->time_taken % 60;
                            echo sprintf(__('%d min %d sec', 'map-drawing-assessment'), $minutes, $seconds);
                            ?>
                        </td>
                        <td>
                            <span class="status-badge status-<?php echo esc_attr($submission->status); ?>">
                                <?php echo esc_html(ucfirst($submission->status)); ?>
                            </span>
                        </td>
                        <td>
                            <a href="<?php echo admin_url('admin.php?page=map-drawing-assessment-submissions&action=view&id=' . $submission->id); ?>" 
                               class="button button-small">
                                <i class="fas fa-eye"></i> <?php _e('View', 'map-drawing-assessment'); ?>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <p class="view-all">
            <a href="<?php echo admin_url('admin.php?page=map-drawing-assessment-submissions'); ?>" class="button">
                <?php _e('View All Submissions', 'map-drawing-assessment'); ?>
            </a>
        </p>
    </div>
    <?php endif; ?>
</div>

<style>
.dashboard-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin: 20px 0;
}

.stat-box {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 15px;
}

.stat-box i {
    font-size: 24px;
    color: #2271b1;
}

.stat-content h3 {
    margin: 0;
    font-size: 24px;
    line-height: 1;
}

.stat-content p {
    margin: 5px 0 0;
    color: #666;
}

.dashboard-actions {
    margin: 30px 0;
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.dashboard-actions .button {
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.recent-submissions {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 20px;
    margin-top: 30px;
}

.recent-submissions h2 {
    margin-top: 0;
}

.status-badge {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
    text-transform: uppercase;
}

.status-submitted {
    background: #e5f6fd;
    color: #0288d1;
}

.status-corrected {
    background: #e8f5e9;
    color: #2e7d32;
}

.view-all {
    margin-top: 20px;
    text-align: right;
}
</style>