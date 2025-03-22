<?php
// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}
?>
<div class="map-drawing-assessment-container">
    <!-- Access Code Modal -->
    <div id="accessCodeModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><?php _e('Enter Access Code', 'map-drawing-assessment'); ?></h2>
            </div>
            <div class="modal-body">
                <form id="accessCodeForm">
                    <?php wp_nonce_field('map_drawing_public_nonce', 'nonce'); ?>
                    <div class="form-field">
                        <label for="accessCode"><?php _e('Access Code', 'map-drawing-assessment'); ?></label>
                        <input type="text" id="accessCode" name="access_code" required>
                        <p class="description"><?php _e('Please enter the access code provided in your email.', 'map-drawing-assessment'); ?></p>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="button button-primary">
                            <i class="fas fa-unlock"></i> <?php _e('Submit', 'map-drawing-assessment'); ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Main Assessment Container -->
    <div id="assessmentContainer" style="display: none;">
        <!-- Question Display -->
        <div class="question-display">
            <div class="question-header">
                <h3 id="questionTitle"></h3>
                <div class="question-timer">
                    <i class="fas fa-clock"></i>
                    <span id="timer">00:00</span>
                </div>
            </div>
            <div id="questionInstructions" class="question-instructions"></div>
        </div>

        <!-- Map Container -->
        <div class="map-wrapper">
            <!-- Top Control Bar -->
            <div class="control-bar top-bar">
                <button type="button" class="control-button" id="toggleDrawing" title="<?php _e('Start/Stop Drawing', 'map-drawing-assessment'); ?>">
                    <i class="fas fa-pencil-alt"></i>
                </button>
                <button type="button" class="control-button" id="undoButton" title="<?php _e('Undo Last Point', 'map-drawing-assessment'); ?>">
                    <i class="fas fa-undo"></i>
                </button>
                <button type="button" class="control-button" id="clearButton" title="<?php _e('Clear Drawing', 'map-drawing-assessment'); ?>">
                    <i class="fas fa-trash-alt"></i>
                </button>
                <div class="brightness-control">
                    <button type="button" class="control-button" id="brightnessButton" title="<?php _e('Adjust Brightness', 'map-drawing-assessment'); ?>">
                        <i class="fas fa-sun"></i>
                    </button>
                    <div class="brightness-slider">
                        <input type="range" id="brightnessSlider" min="0" max="100" value="100">
                    </div>
                </div>
                <button type="button" class="control-button" id="homeButton" title="<?php _e('Reset View', 'map-drawing-assessment'); ?>">
                    <i class="fas fa-home"></i>
                </button>
            </div>

            <!-- Map -->
            <div id="assessmentMap"></div>

            <!-- Bottom Control Bar -->
            <div class="control-bar bottom-bar">
                <div class="left-controls">
                    <button type="button" class="control-button" id="flagButton" title="<?php _e('Flag for Review', 'map-drawing-assessment'); ?>">
                        <i class="fas fa-flag"></i>
                    </button>
                    <button type="button" class="control-button" id="reviewButton" title="<?php _e('Review Questions', 'map-drawing-assessment'); ?>">
                        <i class="fas fa-list-ol"></i>
                    </button>
                </div>
                <div class="center-controls">
                    <button type="button" class="control-button" id="prevButton">
                        <i class="fas fa-chevron-left"></i> <?php _e('Previous', 'map-drawing-assessment'); ?>
                    </button>
                    <button type="button" class="control-button" id="nextButton">
                        <?php _e('Next', 'map-drawing-assessment'); ?> <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
                <div class="right-controls">
                    <button type="button" class="control-button" id="submitButton" style="display: none;">
                        <i class="fas fa-paper-plane"></i> <?php _e('Submit', 'map-drawing-assessment'); ?>
                    </button>
                </div>
            </div>
        </div>

        <!-- MCQ Container -->
        <div id="mcqContainer" class="question-container" style="display: none;">
            <div class="mcq-options"></div>
        </div>

        <!-- Fill in the Blanks Container -->
        <div id="blanksContainer" class="question-container" style="display: none;">
            <div class="blanks-sentence"></div>
            <div class="blanks-options"></div>
        </div>
    </div>

    <!-- Review Modal -->
    <div id="reviewModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><?php _e('Review Questions', 'map-drawing-assessment'); ?></h2>
                <button class="modal-close"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <div id="questionsList" class="questions-grid"></div>
            </div>
        </div>
    </div>
</div>

<style>
.map-drawing-assessment-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
}

/* Question Display */
.question-display {
    margin-bottom: 20px;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    padding: 20px;
}

.question-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.question-header h3 {
    margin: 0;
    font-size: 1.4em;
    color: #1e1e1e;
}

.question-timer {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 1.2em;
    color: #666;
}

.question-instructions {
    color: #4a5568;
    line-height: 1.6;
}

/* Map Container */
.map-wrapper {
    position: relative;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    margin-bottom: 20px;
}

#assessmentMap {
    height: 600px;
    border-radius: 8px;
    z-index: 1;
}

/* Control Bars */
.control-bar {
    position: absolute;
    z-index: 2;
    background: rgba(255, 255, 255, 0.9);
    padding: 10px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    display: flex;
    gap: 10px;
}

.top-bar {
    top: 10px;
    left: 10px;
}

.bottom-bar {
    bottom: 10px;
    left: 10px;
    right: 10px;
    justify-content: space-between;
    align-items: center;
}

.control-button {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 8px 12px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 5px;
    transition: all 0.2s ease;
}

.control-button:hover {
    background: #f8f9fa;
    border-color: #c1c1c1;
}

.control-button.active {
    background: #e9ecef;
    border-color: #adb5bd;
}

/* Brightness Control */
.brightness-control {
    position: relative;
}

.brightness-slider {
    display: none;
    position: absolute;
    top: 100%;
    left: 50%;
    transform: translateX(-50%);
    background: #fff;
    padding: 10px;
    border-radius: 4px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    margin-top: 5px;
}

.brightness-control:hover .brightness-slider {
    display: block;
}

/* Question Types */
.question-container {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    padding: 20px;
    margin-bottom: 20px;
}

/* MCQ Styles */
.mcq-options {
    display: grid;
    gap: 10px;
}

.mcq-option {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.mcq-option:hover {
    background: #f8f9fa;
}

.mcq-option.selected {
    background: #e9ecef;
    border-color: #adb5bd;
}

/* Fill in the Blanks Styles */
.blanks-sentence {
    font-size: 1.1em;
    line-height: 1.8;
    margin-bottom: 20px;
}

.blank-dropdown {
    display: inline-block;
    min-width: 150px;
    margin: 0 5px;
}

.blanks-options {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 20px;
}

/* Review Modal */
.questions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(60px, 1fr));
    gap: 10px;
    padding: 20px;
}

.question-number {
    width: 100%;
    aspect-ratio: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 4px;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.2s ease;
}

.question-number:hover {
    background: #f8f9fa;
}

.question-number.completed {
    background: #dcfce7;
    border-color: #166534;
    color: #166534;
}

.question-number.flagged {
    background: #fee2e2;
    border-color: #991b1b;
    color: #991b1b;
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
    border-radius: 8px;
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

/* Responsive Design */
@media screen and (max-width: 768px) {
    .map-drawing-assessment-container {
        padding: 10px;
    }

    #assessmentMap {
        height: 400px;
    }

    .control-bar {
        flex-wrap: wrap;
    }

    .bottom-bar {
        flex-direction: column;
        gap: 15px;
    }

    .center-controls {
        order: -1;
        width: 100%;
        display: flex;
        justify-content: space-between;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    let map;
    let drawing = false;
    let currentPath = [];
    let allPaths = [];
    let currentQuestion = 0;
    let questions = <?php echo wp_json_encode($assessment_questions); ?>;
    let startTime = Date.now();
    let timerInterval;
    let flaggedQuestions = new Set();

    // Initialize timer
    function startTimer() {
        timerInterval = setInterval(updateTimer, 1000);
    }

    function updateTimer() {
        const elapsed = Math.floor((Date.now() - startTime) / 1000);
        const minutes = Math.floor(elapsed / 60);
        const seconds = elapsed % 60;
        $('#timer').text(`${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`);
    }

    // Initialize map
    function initializeMap() {
        map = L.map('assessmentMap').setView([51.5074, -0.1278], 13);
        
        L.tileLayer('<?php echo MAP_DRAWING_CUSTOM_MAP_URL; ?>', {
            maxZoom: 19,
            attribution: '© SERU Map'
        }).addTo(map);

        // Map click handler for drawing
        map.on('click', function(e) {
            if (!drawing) return;

            const latlng = e.latlng;
            currentPath.push([latlng.lat, latlng.lng]);
            redrawPath();
        });
    }

    // Drawing functions
    function redrawPath() {
        map.eachLayer((layer) => {
            if (layer instanceof L.Polyline) {
                map.removeLayer(layer);
            }
        });

        if (currentPath.length > 0) {
            L.polyline(currentPath, {color: 'blue'}).addTo(map);
        }
    }

    // Control button handlers
    $('#toggleDrawing').click(function() {
        drawing = !drawing;
        $(this).toggleClass('active');
    });

    $('#undoButton').click(function() {
        if (currentPath.length > 0) {
            currentPath.pop();
            redrawPath();
        }
    });

    $('#clearButton').click(function() {
        if (confirm('<?php _e('Are you sure you want to clear your drawing?', 'map-drawing-assessment'); ?>')) {
            currentPath = [];
            redrawPath();
        }
    });

    $('#brightnessButton').click(function() {
        $('.brightness-slider').toggle();
    });

    $('#brightnessSlider').on('input', function() {
        const brightness = $(this).val();
        $('.leaflet-tile').css('filter', `brightness(${brightness}%)`);
    });

    $('#homeButton').click(function() {
        if (questions[currentQuestion].type === 'map') {
            const bounds = L.latLngBounds([
                questions[currentQuestion].content.start_marker,
                questions[currentQuestion].content.end_marker
            ]);
            map.fitBounds(bounds.pad(0.1));
        }
    });

    $('#flagButton').click(function() {
        $(this).toggleClass('active');
        if ($(this).hasClass('active')) {
            flaggedQuestions.add(currentQuestion);
        } else {
            flaggedQuestions.delete(currentQuestion);
        }
        updateQuestionsList();
    });

    $('#reviewButton').click(function() {
        $('#reviewModal').show();
    });

    // Navigation
    function loadQuestion(index) {
        const question = questions[index];
        $('#questionTitle').text(question.title);

        // Clear previous question content
        $('#questionInstructions').empty();
        $('.question-container').hide();
        map.eachLayer((layer) => {
            if (layer instanceof L.Marker || layer instanceof L.Polyline) {
                map.removeLayer(layer);
            }
        });

        // Load question based on type
        switch (question.type) {
            case 'map':
                $('.map-wrapper').show();
                $('#questionInstructions').html(question.content.instructions);
                
                // Add markers
                L.marker([question.content.start_marker.lat, question.content.start_marker.lng], {
                    icon: L.icon({
                        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-green.png',
                        iconSize: [25, 41],
                        iconAnchor: [12, 41]
                    })
                }).addTo(map);

                L.marker([question.content.end_marker.lat, question.content.end_marker.lng], {
                    icon: L.icon({
                        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
                        iconSize: [25, 41],
                        iconAnchor: [12, 41]
                    })
                }).addTo(map);

                // Fit bounds to show markers
                const bounds = L.latLngBounds([
                    [question.content.start_marker.lat, question.content.start_marker.lng],
                    [question.content.end_marker.lat, question.content.end_marker.lng]
                ]);
                map.fitBounds(bounds.pad(0.1));
                break;

            case 'mcq':
                $('.map-wrapper').hide();
                $('#mcqContainer').show();
                $('#questionInstructions').html(question.content.question);
                
                const optionsHtml = question.content.options.map((option, i) => `
                    <label class="mcq-option">
                        <input type="checkbox" name="mcq_answer" value="${i}">
                        <span>${option}</span>
                    </label>
                `).join('');
                $('.mcq-options').html(optionsHtml);
                break;

            case 'blanks':
                $('.map-wrapper').hide();
                $('#blanksContainer').show();
                
                // Split sentence by [BLANK] and create dropdowns
                const parts = question.content.sentence.split('[BLANK]');
                let sentenceHtml = parts[0];
                for (let i = 1; i < parts.length; i++) {
                    sentenceHtml += `
                        <select class="blank-dropdown" data-blank="${i-1}">
                            <option value=""><?php _e('Select answer', 'map-drawing-assessment'); ?></option>
                            ${question.content.options.map((option, j) => `
                                <option value="${j}">${option}</option>
                            `).join('')}
                        </select>
                    ` + parts[i];
                }
                $('.blanks-sentence').html(sentenceHtml);
                break;
        }

        // Update navigation buttons
        $('#prevButton').prop('disabled', index === 0);
        $('#nextButton').prop('disabled', index === questions.length - 1);
        $('#submitButton').toggle(index === questions.length - 1);

        // Update flag button state
        $('#flagButton').toggleClass('active', flaggedQuestions.has(index));

        currentQuestion = index;
        updateQuestionsList();
    }

    $('#prevButton').click(function() {
        if (currentQuestion > 0) {
            saveCurrentProgress();
            loadQuestion(currentQuestion - 1);
        }
    });

    $('#nextButton').click(function() {
        if (currentQuestion < questions.length - 1) {
            saveCurrentProgress();
            loadQuestion(currentQuestion + 1);
        }
    });

    // Save progress
    function saveCurrentProgress() {
        const question = questions[currentQuestion];
        let answer;

        switch (question.type) {
            case 'map':
                answer = currentPath;
                break;
            case 'mcq':
                answer = $('input[name="mcq_answer"]:checked').map(function() {
                    return parseInt($(this).val());
                }).get();
                break;
            case 'blanks':
                answer = $('.blank-dropdown').map(function() {
                    return $(this).val();
                }).get();
                break;
        }

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'save_drawing',
                nonce: '<?php echo wp_create_nonce("map_drawing_public_nonce"); ?>',
                question_id: question.id,
                drawing: answer,
                is_flagged: flaggedQuestions.has(currentQuestion)
            }
        });
    }

    // Submit assessment
    $('#submitButton').click(function() {
        if (!confirm('<?php _e('Are you sure you want to submit your assessment? This action cannot be undone.', 'map-drawing-assessment'); ?>')) {
            return;
        }

        saveCurrentProgress();

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'submit_assessment',
                nonce: '<?php echo wp_create_nonce("map_drawing_public_nonce"); ?>',
                questions: questions,
                time_taken: Math.floor((Date.now() - startTime) / 1000)
            },
            success: function(response) {
                if (response.success) {
                    clearInterval(timerInterval);
                    alert('<?php _e('Assessment submitted successfully!', 'map-drawing-assessment'); ?>');
                    location.reload();
                } else {
                    alert(response.data || '<?php _e('Error submitting assessment.', 'map-drawing-assessment'); ?>');
                }
            },
            error: function() {
                alert('<?php _e('Error submitting assessment.', 'map-drawing-assessment'); ?>');
            }
        });
    });

    // Review modal
    function updateQuestionsList() {
        const html = questions.map((q, i) => `
            <button type="button" class="question-number ${i === currentQuestion ? 'current' : ''} 
                ${flaggedQuestions.has(i) ? 'flagged' : ''}" data-index="${i}">
                ${i + 1}
            </button>
        `).join('');
        $('#questionsList').html(html);
    }

    $(document).on('click', '.question-number', function() {
        const index = $(this).data('index');
        saveCurrentProgress();
        loadQuestion(index);
        $('#reviewModal').hide();
    });

    // Close modals
    $('.modal-close, .modal').click(function(e) {
        if (e.target === this) {
            $(this).closest('.modal').hide();
        }
    });

    // Check access code
    function checkAccessCode() {
        const accessCode = getCookie('map_assessment_access_code');
        if (accessCode) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'validate_access_code',
                    nonce: '<?php echo wp_create_nonce("map_drawing_public_nonce"); ?>',
                    access_code: accessCode
                },
                success: function(response) {
                    if (response.success) {
                        $('#accessCodeModal').hide();
                        $('#assessmentContainer').show();
                        initializeMap();
                        loadQuestion(0);
                        startTimer();
                    } else {
                        $('#accessCodeModal').show();
                    }
                }
            });
        } else {
            $('#accessCodeModal').show();
        }
    }

    // Access code form submission
    $('#accessCodeForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'validate_access_code',
                ...$(this).serializeArray()
            },
            success: function(response) {
                if (response.success) {
                    setCookie('map_assessment_access_code', $('#accessCode').val(), 2);
                    $('#accessCodeModal').hide();
                    $('#assessmentContainer').show();
                    initializeMap();
                    loadQuestion(0);
                    startTimer();
                } else {
                    alert(response.data || '<?php _e('Invalid access code.', 'map-drawing-assessment'); ?>');
                }
            }
        });
    });

    // Cookie helpers
    function setCookie(name, value, days) {
        const expires = new Date();
        expires.setTime(expires.getTime() + (days * 24 * 60 * 60 * 1000));
        document.cookie = `${name}=${value};expires=${expires.toUTCString()};path=/`;
    }

    function getCookie(name) {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) return parts.pop().split(';').shift();
    }

    // Initialize
    checkAccessCode();
});
</script>