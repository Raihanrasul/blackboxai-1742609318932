<?php
// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Get submissions with filters
$filters = array(
    'user_id' => isset($_GET['user_id']) ? intval($_GET['user_id']) : null,
    'status' => isset($_GET['status']) ? sanitize_text_field($_GET['status']) : null
);

$submissions = new Map_Drawing_Assessment_Submissions();
$all_submissions = $submissions->get_submissions($filters);

// Get all users with submissions
global $wpdb;
$users_with_submissions = $wpdb->get_results("
    SELECT DISTINCT u.ID, u.display_name 
    FROM {$wpdb->users} u 
    INNER JOIN {$wpdb->prefix}map_drawing_submissions s ON u.ID = s.user_id
    ORDER BY u.display_name
");
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php _e('Assessment Submissions', 'map-drawing-assessment'); ?></h1>
    <hr class="wp-header-end">

    <!-- Filters -->
    <div class="tablenav top">
        <div class="alignleft actions">
            <form method="get" class="filter-form">
                <input type="hidden" name="page" value="map-drawing-assessment-submissions">
                
                <select name="user_id">
                    <option value=""><?php _e('All Users', 'map-drawing-assessment'); ?></option>
                    <?php foreach ($users_with_submissions as $user): ?>
                        <option value="<?php echo $user->ID; ?>" <?php selected($filters['user_id'], $user->ID); ?>>
                            <?php echo esc_html($user->display_name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <select name="status">
                    <option value=""><?php _e('All Statuses', 'map-drawing-assessment'); ?></option>
                    <option value="submitted" <?php selected($filters['status'], 'submitted'); ?>><?php _e('Submitted', 'map-drawing-assessment'); ?></option>
                    <option value="corrected" <?php selected($filters['status'], 'corrected'); ?>><?php _e('Corrected', 'map-drawing-assessment'); ?></option>
                </select>

                <button type="submit" class="button"><?php _e('Filter', 'map-drawing-assessment'); ?></button>
            </form>
        </div>
        <div class="tablenav-pages">
            <!-- Pagination could be added here if needed -->
        </div>
    </div>

    <!-- Submissions Table -->
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th scope="col" class="manage-column column-user"><?php _e('User', 'map-drawing-assessment'); ?></th>
                <th scope="col" class="manage-column column-date"><?php _e('Submission Date', 'map-drawing-assessment'); ?></th>
                <th scope="col" class="manage-column column-time"><?php _e('Time Taken', 'map-drawing-assessment'); ?></th>
                <th scope="col" class="manage-column column-marks"><?php _e('Marks', 'map-drawing-assessment'); ?></th>
                <th scope="col" class="manage-column column-status"><?php _e('Status', 'map-drawing-assessment'); ?></th>
                <th scope="col" class="manage-column column-actions"><?php _e('Actions', 'map-drawing-assessment'); ?></th>
            </tr>
        </thead>

        <tbody>
            <?php if (!empty($all_submissions)): ?>
                <?php foreach ($all_submissions as $submission): ?>
                    <tr>
                        <td class="column-user">
                            <?php echo esc_html($submission->user_name); ?>
                        </td>
                        <td class="column-date">
                            <?php echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($submission->created_at)); ?>
                        </td>
                        <td class="column-time">
                            <?php 
                            $minutes = floor($submission->time_taken / 60);
                            $seconds = $submission->time_taken % 60;
                            echo sprintf(__('%d min %d sec', 'map-drawing-assessment'), $minutes, $seconds);
                            ?>
                        </td>
                        <td class="column-marks">
                            <?php echo isset($submission->marks) ? esc_html($submission->marks) : '-'; ?>
                        </td>
                        <td class="column-status">
                            <span class="status-badge status-<?php echo esc_attr($submission->status); ?>">
                                <?php echo esc_html(ucfirst($submission->status)); ?>
                            </span>
                        </td>
                        <td class="column-actions">
                            <div class="row-actions">
                                <button type="button" class="button button-small view-submission" 
                                        data-submission-id="<?php echo esc_attr($submission->id); ?>"
                                        data-user-id="<?php echo esc_attr($submission->user_id); ?>">
                                    <i class="fas fa-eye"></i> <?php _e('View', 'map-drawing-assessment'); ?>
                                </button>
                                <?php if ($submission->status === 'corrected'): ?>
                                    <button type="button" class="button button-small send-result" 
                                            data-submission-id="<?php echo esc_attr($submission->id); ?>"
                                            data-user-id="<?php echo esc_attr($submission->user_id); ?>">
                                        <i class="fas fa-envelope"></i> <?php _e('Send Result', 'map-drawing-assessment'); ?>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6"><?php _e('No submissions found.', 'map-drawing-assessment'); ?></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Submission View Modal -->
<div id="submissionModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2><?php _e('Submission Details', 'map-drawing-assessment'); ?></h2>
            <button class="modal-close"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <div id="submissionContent">
                <!-- Content will be loaded dynamically -->
            </div>
        </div>
    </div>
</div>

<style>
.filter-form {
    display: flex;
    gap: 10px;
    align-items: center;
}

.filter-form select {
    max-width: 200px;
}

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

.status-submitted {
    background: #fef3c7;
    color: #92400e;
}

.status-corrected {
    background: #dcfce7;
    color: #166534;
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
    max-width: 900px;
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
    max-height: 70vh;
    overflow-y: auto;
}

#submissionMap {
    height: 400px;
    margin-bottom: 20px;
    border-radius: 4px;
}

.correction-form {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #ddd;
}

.correction-form .form-field {
    margin-bottom: 15px;
}

.correction-form label {
    display: block;
    font-weight: 600;
    margin-bottom: 5px;
}

.correction-form textarea {
    width: 100%;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.correction-form input[type="number"] {
    width: 100px;
}
</style>

<script>
jQuery(document).ready(function($) {
    // View submission
    $('.view-submission').click(function() {
        const submissionId = $(this).data('submission-id');
        const userId = $(this).data('user-id');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'get_submission_details',
                nonce: '<?php echo wp_create_nonce("map_drawing_admin_nonce"); ?>',
                submission_id: submissionId,
                user_id: userId
            },
            success: function(response) {
                if (response.success) {
                    $('#submissionContent').html(response.data.html);
                    initializeSubmissionMap();
                    $('#submissionModal').show();
                } else {
                    alert(response.data || '<?php _e('Error loading submission details.', 'map-drawing-assessment'); ?>');
                }
            },
            error: function() {
                alert('<?php _e('Error loading submission details.', 'map-drawing-assessment'); ?>');
            }
        });
    });

    // Send result email
    $('.send-result').click(function() {
        if (!confirm('<?php _e('Are you sure you want to send the result email?', 'map-drawing-assessment'); ?>')) {
            return;
        }

        const submissionId = $(this).data('submission-id');
        const userId = $(this).data('user-id');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'send_result_email',
                nonce: '<?php echo wp_create_nonce("map_drawing_admin_nonce"); ?>',
                submission_id: submissionId,
                user_id: userId
            },
            success: function(response) {
                if (response.success) {
                    alert('<?php _e('Result email sent successfully!', 'map-drawing-assessment'); ?>');
                } else {
                    alert(response.data || '<?php _e('Error sending result email.', 'map-drawing-assessment'); ?>');
                }
            },
            error: function() {
                alert('<?php _e('Error sending result email.', 'map-drawing-assessment'); ?>');
            }
        });
    });

    // Close modal
    $('.modal-close, .modal').click(function(e) {
        if (e.target === this) {
            $('#submissionModal').hide();
        }
    });

    // Initialize submission map
    function initializeSubmissionMap() {
        if (!$('#submissionMap').length) return;

        const map = L.map('submissionMap').setView([51.5074, -0.1278], 13);
        
        L.tileLayer('<?php echo MAP_DRAWING_CUSTOM_MAP_URL; ?>', {
            maxZoom: 19,
            attribution: '© SERU Map'
        }).addTo(map);

        // Add route and markers from the data attributes
        const mapData = $('#submissionMap').data();
        
        if (mapData.startMarker) {
            L.marker([mapData.startMarker.lat, mapData.startMarker.lng], {
                icon: L.icon({
                    iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-green.png',
                    iconSize: [25, 41],
                    iconAnchor: [12, 41]
                })
            }).addTo(map);
        }

        if (mapData.endMarker) {
            L.marker([mapData.endMarker.lat, mapData.endMarker.lng], {
                icon: L.icon({
                    iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
                    iconSize: [25, 41],
                    iconAnchor: [12, 41]
                })
            }).addTo(map);
        }

        if (mapData.route) {
            L.polyline(mapData.route, {color: 'blue'}).addTo(map);
        }

        if (mapData.correctedRoute) {
            L.polyline(mapData.correctedRoute, {color: 'green'}).addTo(map);
        }

        // Fit bounds to show all markers and routes
        const bounds = L.latLngBounds([]);
        if (mapData.startMarker) bounds.extend([mapData.startMarker.lat, mapData.startMarker.lng]);
        if (mapData.endMarker) bounds.extend([mapData.endMarker.lat, mapData.endMarker.lng]);
        if (bounds.isValid()) map.fitBounds(bounds.pad(0.1));
    }

    // Save correction
    $(document).on('submit', '#correctionForm', function(e) {
        e.preventDefault();

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'save_correction',
                nonce: '<?php echo wp_create_nonce("map_drawing_admin_nonce"); ?>',
                ...$(this).serializeArray()
            },
            success: function(response) {
                if (response.success) {
                    alert('<?php _e('Correction saved successfully!', 'map-drawing-assessment'); ?>');
                    location.reload();
                } else {
                    alert(response.data || '<?php _e('Error saving correction.', 'map-drawing-assessment'); ?>');
                }
            },
            error: function() {
                alert('<?php _e('Error saving correction.', 'map-drawing-assessment'); ?>');
            }
        });
    });
});
</script>