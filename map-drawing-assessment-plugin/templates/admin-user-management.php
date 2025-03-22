<?php
// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Get all users with their assessment access status
$users = get_users(array('orderby' => 'display_name'));
foreach ($users as $user) {
    $user->assessment_access = get_user_meta($user->ID, 'map_assessment_access', true);
    $user->access_expiry = get_user_meta($user->ID, 'map_assessment_access_expiry', true);
    $user->access_period = get_user_meta($user->ID, 'map_assessment_access_period', true);
}
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php _e('User Management', 'map-drawing-assessment'); ?></h1>
    <hr class="wp-header-end">

    <!-- User Management Table -->
    <div class="tablenav top">
        <div class="alignleft actions">
            <button type="button" class="button add-user-access">
                <i class="fas fa-plus"></i> <?php _e('Grant Access', 'map-drawing-assessment'); ?>
            </button>
        </div>
        <div class="tablenav-pages">
            <!-- Pagination could be added here if needed -->
        </div>
    </div>

    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th scope="col" class="manage-column column-username"><?php _e('Username', 'map-drawing-assessment'); ?></th>
                <th scope="col" class="manage-column column-name"><?php _e('Name', 'map-drawing-assessment'); ?></th>
                <th scope="col" class="manage-column column-email"><?php _e('Email', 'map-drawing-assessment'); ?></th>
                <th scope="col" class="manage-column column-access"><?php _e('Access Status', 'map-drawing-assessment'); ?></th>
                <th scope="col" class="manage-column column-expiry"><?php _e('Access Expiry', 'map-drawing-assessment'); ?></th>
                <th scope="col" class="manage-column column-actions"><?php _e('Actions', 'map-drawing-assessment'); ?></th>
            </tr>
        </thead>

        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td class="column-username">
                        <?php echo esc_html($user->user_login); ?>
                    </td>
                    <td class="column-name">
                        <?php echo esc_html($user->display_name); ?>
                    </td>
                    <td class="column-email">
                        <?php echo esc_html($user->user_email); ?>
                    </td>
                    <td class="column-access">
                        <?php if ($user->assessment_access === 'active'): ?>
                            <span class="status-badge status-active"><?php _e('Active', 'map-drawing-assessment'); ?></span>
                        <?php else: ?>
                            <span class="status-badge status-inactive"><?php _e('Inactive', 'map-drawing-assessment'); ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="column-expiry">
                        <?php
                        if ($user->access_expiry) {
                            $expiry_date = strtotime($user->access_expiry);
                            $now = current_time('timestamp');
                            if ($expiry_date > $now) {
                                echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $expiry_date);
                                $days_left = ceil(($expiry_date - $now) / DAY_IN_SECONDS);
                                echo ' <span class="days-left">(' . sprintf(_n('%d day left', '%d days left', $days_left, 'map-drawing-assessment'), $days_left) . ')</span>';
                            } else {
                                echo '<span class="expired">' . __('Expired', 'map-drawing-assessment') . '</span>';
                            }
                        } else {
                            echo '-';
                        }
                        ?>
                    </td>
                    <td class="column-actions">
                        <div class="row-actions">
                            <?php if ($user->assessment_access === 'active'): ?>
                                <button type="button" class="button button-small extend-access" 
                                        data-user-id="<?php echo esc_attr($user->ID); ?>"
                                        data-user-name="<?php echo esc_attr($user->display_name); ?>">
                                    <i class="fas fa-clock"></i> <?php _e('Extend', 'map-drawing-assessment'); ?>
                                </button>
                                <button type="button" class="button button-small revoke-access" 
                                        data-user-id="<?php echo esc_attr($user->ID); ?>"
                                        data-user-name="<?php echo esc_attr($user->display_name); ?>">
                                    <i class="fas fa-ban"></i> <?php _e('Revoke', 'map-drawing-assessment'); ?>
                                </button>
                            <?php else: ?>
                                <button type="button" class="button button-small grant-access" 
                                        data-user-id="<?php echo esc_attr($user->ID); ?>"
                                        data-user-name="<?php echo esc_attr($user->display_name); ?>">
                                    <i class="fas fa-key"></i> <?php _e('Grant Access', 'map-drawing-assessment'); ?>
                                </button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Access Modal -->
<div id="accessModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitle"><?php _e('Grant Access', 'map-drawing-assessment'); ?></h2>
            <button class="modal-close"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <form id="accessForm">
                <?php wp_nonce_field('map_drawing_admin_nonce', 'nonce'); ?>
                <input type="hidden" name="user_id" id="userId">
                <input type="hidden" name="action_type" id="actionType">

                <div class="form-field">
                    <label for="accessPeriod"><?php _e('Access Period (Days)', 'map-drawing-assessment'); ?> <span class="required">*</span></label>
                    <input type="number" id="accessPeriod" name="access_period" min="1" required>
                    <p class="description"><?php _e('Number of days the user will have access to the assessment.', 'map-drawing-assessment'); ?></p>
                </div>

                <div class="form-field">
                    <label>
                        <input type="checkbox" name="send_email" checked>
                        <?php _e('Send email notification to user', 'map-drawing-assessment'); ?>
                    </label>
                </div>

                <div class="form-actions">
                    <button type="submit" class="button button-primary">
                        <i class="fas fa-save"></i> <span id="submitButtonText"><?php _e('Grant Access', 'map-drawing-assessment'); ?></span>
                    </button>
                    <button type="button" class="button modal-close-button">
                        <i class="fas fa-times"></i> <?php _e('Cancel', 'map-drawing-assessment'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.column-actions {
    width: 200px;
}

.row-actions {
    display: flex;
    gap: 10px;
}

.status-badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
}

.status-active {
    background: #dcfce7;
    color: #166534;
}

.status-inactive {
    background: #fee2e2;
    color: #991b1b;
}

.days-left {
    color: #666;
    font-size: 12px;
}

.expired {
    color: #dc2626;
    font-weight: 500;
}

/* Modal Styles */
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 100000;
}

.modal-content {
    position: relative;
    background: #fff;
    margin: 50px auto;
    padding: 0;
    width: 90%;
    max-width: 500px;
    border-radius: 4px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
}

.modal-header {
    padding: 15px 20px;
    border-bottom: 1px solid #ddd;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h2 {
    margin: 0;
    font-size: 1.3em;
}

.modal-close {
    background: none;
    border: none;
    cursor: pointer;
    font-size: 20px;
    color: #666;
}

.modal-body {
    padding: 20px;
}

.form-field {
    margin-bottom: 15px;
}

.form-field label {
    display: block;
    font-weight: 600;
    margin-bottom: 5px;
}

.form-field input[type="number"] {
    width: 100px;
}

.form-actions {
    display: flex;
    gap: 10px;
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #ddd;
}

.required {
    color: #d63638;
}

.description {
    font-size: 13px;
    color: #646970;
    margin-top: 4px;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Show access modal
    function showAccessModal(title, buttonText, actionType, userId, userName) {
        $('#modalTitle').text(title);
        $('#submitButtonText').text(buttonText);
        $('#actionType').val(actionType);
        $('#userId').val(userId);
        $('#accessModal').show();
    }

    // Grant access button click
    $('.grant-access, .add-user-access').click(function() {
        const userId = $(this).data('user-id');
        const userName = $(this).data('user-name');
        showAccessModal(
            '<?php _e('Grant Access', 'map-drawing-assessment'); ?>', 
            '<?php _e('Grant Access', 'map-drawing-assessment'); ?>', 
            'grant', 
            userId,
            userName
        );
    });

    // Extend access button click
    $('.extend-access').click(function() {
        const userId = $(this).data('user-id');
        const userName = $(this).data('user-name');
        showAccessModal(
            '<?php _e('Extend Access', 'map-drawing-assessment'); ?>', 
            '<?php _e('Extend Access', 'map-drawing-assessment'); ?>', 
            'extend', 
            userId,
            userName
        );
    });

    // Revoke access button click
    $('.revoke-access').click(function() {
        if (!confirm('<?php _e('Are you sure you want to revoke access for this user?', 'map-drawing-assessment'); ?>')) {
            return;
        }

        const userId = $(this).data('user-id');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'revoke_user_access',
                nonce: '<?php echo wp_create_nonce("map_drawing_admin_nonce"); ?>',
                user_id: userId
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.data || '<?php _e('Error revoking access.', 'map-drawing-assessment'); ?>');
                }
            },
            error: function() {
                alert('<?php _e('Error revoking access.', 'map-drawing-assessment'); ?>');
            }
        });
    });

    // Close modal
    $('.modal-close, .modal-close-button, .modal').click(function(e) {
        if (e.target === this) {
            $('#accessModal').hide();
        }
    });

    // Form submission
    $('#accessForm').on('submit', function(e) {
        e.preventDefault();

        const actionType = $('#actionType').val();
        const action = actionType === 'grant' ? 'grant_user_access' : 'extend_user_access';

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: action,
                ...$(this).serializeArray()
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.data || '<?php _e('Error updating access.', 'map-drawing-assessment'); ?>');
                }
            },
            error: function() {
                alert('<?php _e('Error updating access.', 'map-drawing-assessment'); ?>');
            }
        });
    });
});
</script>