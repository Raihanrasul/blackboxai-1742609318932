<?php
// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}
?>
<div class="wrap">
    <h1 class="wp-heading-inline"><?php _e('Create Map Question', 'map-drawing-assessment'); ?></h1>
    <hr class="wp-header-end">

    <div class="map-question-container">
        <div class="map-question-form">
            <div class="postbox">
                <div class="inside">
                    <form id="mapQuestionForm">
                        <?php wp_nonce_field('map_drawing_admin_nonce', 'nonce'); ?>
                        
                        <div class="form-field">
                            <label for="questionTitle"><?php _e('Question Title', 'map-drawing-assessment'); ?> <span class="required">*</span></label>
                            <input type="text" id="questionTitle" name="title" required>
                            <p class="description"><?php _e('Enter a descriptive title for the question.', 'map-drawing-assessment'); ?></p>
                        </div>

                        <div class="form-field">
                            <label for="questionInstructions"><?php _e('Instructions', 'map-drawing-assessment'); ?> <span class="required">*</span></label>
                            <textarea id="questionInstructions" name="instructions" rows="4" required></textarea>
                            <p class="description"><?php _e('Provide clear instructions for the route to be drawn.', 'map-drawing-assessment'); ?></p>
                        </div>

                        <div class="form-field">
                            <label><?php _e('Map Markers', 'map-drawing-assessment'); ?></label>
                            <div class="marker-coordinates">
                                <div class="start-marker">
                                    <h4><i class="fas fa-map-marker-alt green"></i> <?php _e('Start Point', 'map-drawing-assessment'); ?></h4>
                                    <div class="coordinate-inputs">
                                        <input type="number" id="startLat" name="start_lat" step="any" placeholder="Latitude" readonly>
                                        <input type="number" id="startLng" name="start_lng" step="any" placeholder="Longitude" readonly>
                                    </div>
                                </div>
                                <div class="end-marker">
                                    <h4><i class="fas fa-map-marker-alt red"></i> <?php _e('End Point', 'map-drawing-assessment'); ?></h4>
                                    <div class="coordinate-inputs">
                                        <input type="number" id="endLat" name="end_lat" step="any" placeholder="Latitude" readonly>
                                        <input type="number" id="endLng" name="end_lng" step="any" placeholder="Longitude" readonly>
                                    </div>
                                </div>
                            </div>
                            <p class="description"><?php _e('Click on the map to place start (green) and end (red) markers.', 'map-drawing-assessment'); ?></p>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="button button-primary">
                                <i class="fas fa-save"></i> <?php _e('Save Question', 'map-drawing-assessment'); ?>
                            </button>
                            <button type="button" class="button" id="resetMarkers">
                                <i class="fas fa-undo"></i> <?php _e('Reset Markers', 'map-drawing-assessment'); ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="map-container">
            <div class="postbox">
                <h2 class="hndle"><span><?php _e('Map Preview', 'map-drawing-assessment'); ?></span></h2>
                <div class="inside">
                    <div id="questionMap"></div>
                    <div class="map-controls">
                        <button type="button" class="button" id="zoomIn">
                            <i class="fas fa-plus"></i>
                        </button>
                        <button type="button" class="button" id="zoomOut">
                            <i class="fas fa-minus"></i>
                        </button>
                        <button type="button" class="button" id="centerMap">
                            <i class="fas fa-crosshairs"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.map-question-container {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-top: 20px;
}

.form-field {
    margin-bottom: 20px;
}

.form-field label {
    display: block;
    font-weight: 600;
    margin-bottom: 5px;
}

.form-field input[type="text"],
.form-field textarea {
    width: 100%;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.marker-coordinates {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 4px;
    margin-top: 10px;
}

.start-marker,
.end-marker {
    margin-bottom: 15px;
}

.coordinate-inputs {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
}

.coordinate-inputs input {
    width: 100%;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
    background: #fff;
}

#questionMap {
    height: 500px;
    border-radius: 4px;
    margin-bottom: 10px;
}

.map-controls {
    display: flex;
    gap: 10px;
    margin-top: 10px;
}

.form-actions {
    display: flex;
    gap: 10px;
    margin-top: 20px;
}

.required {
    color: #d63638;
}

.green {
    color: #00a32a;
}

.red {
    color: #d63638;
}

.description {
    font-size: 13px;
    color: #646970;
    margin-top: 4px;
}

/* Responsive Design */
@media screen and (max-width: 1200px) {
    .map-question-container {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    // Initialize map
    var map = L.map('questionMap').setView([51.5074, -0.1278], 13);
    
    // Add custom map tiles
    L.tileLayer('<?php echo MAP_DRAWING_CUSTOM_MAP_URL; ?>', {
        maxZoom: 19,
        attribution: '© SERU Map'
    }).addTo(map);

    var startMarker = null;
    var endMarker = null;
    var markerStep = 'start'; // 'start' or 'end'

    // Custom markers
    var greenIcon = L.icon({
        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-green.png',
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        popupAnchor: [1, -34]
    });

    var redIcon = L.icon({
        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        popupAnchor: [1, -34]
    });

    // Map click handler
    map.on('click', function(e) {
        var latlng = e.latlng;

        if (markerStep === 'start') {
            if (startMarker) {
                map.removeLayer(startMarker);
            }
            startMarker = L.marker(latlng, {icon: greenIcon}).addTo(map);
            $('#startLat').val(latlng.lat.toFixed(6));
            $('#startLng').val(latlng.lng.toFixed(6));
            markerStep = 'end';
        } else {
            if (endMarker) {
                map.removeLayer(endMarker);
            }
            endMarker = L.marker(latlng, {icon: redIcon}).addTo(map);
            $('#endLat').val(latlng.lat.toFixed(6));
            $('#endLng').val(latlng.lng.toFixed(6));
            markerStep = 'start';
        }
    });

    // Map controls
    $('#zoomIn').click(function() {
        map.zoomIn();
    });

    $('#zoomOut').click(function() {
        map.zoomOut();
    });

    $('#centerMap').click(function() {
        map.setView([51.5074, -0.1278], 13);
    });

    // Reset markers
    $('#resetMarkers').click(function() {
        if (startMarker) {
            map.removeLayer(startMarker);
            startMarker = null;
            $('#startLat, #startLng').val('');
        }
        if (endMarker) {
            map.removeLayer(endMarker);
            endMarker = null;
            $('#endLat, #endLng').val('');
        }
        markerStep = 'start';
    });

    // Form submission
    $('#mapQuestionForm').on('submit', function(e) {
        e.preventDefault();

        if (!startMarker || !endMarker) {
            alert('<?php _e('Please place both start and end markers on the map.', 'map-drawing-assessment'); ?>');
            return;
        }

        var formData = $(this).serializeArray();
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'save_map_question',
                ...Object.fromEntries(formData)
            },
            success: function(response) {
                if (response.success) {
                    alert('<?php _e('Question saved successfully!', 'map-drawing-assessment'); ?>');
                    location.reload();
                } else {
                    alert(response.data || '<?php _e('Error saving question.', 'map-drawing-assessment'); ?>');
                }
            },
            error: function() {
                alert('<?php _e('Error saving question.', 'map-drawing-assessment'); ?>');
            }
        });
    });
});
</script>