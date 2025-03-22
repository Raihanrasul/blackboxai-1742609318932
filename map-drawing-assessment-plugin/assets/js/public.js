/**
 * Public JavaScript for Map Drawing Assessment plugin
 */
(function($) {
    'use strict';

    // Global variables
    let map;
    let drawing = false;
    let currentPath = [];
    let allPaths = [];
    let currentQuestion = 0;
    let questions = [];
    let startTime = Date.now();
    let timerInterval;
    let flaggedQuestions = new Set();
    let autosaveInterval;

    // Initialize assessment
    function initAssessment(assessmentQuestions) {
        questions = assessmentQuestions;
        startTimer();
        initAutosave();
        loadQuestion(0);
    }

    // Timer functions
    function startTimer() {
        timerInterval = setInterval(updateTimer, 1000);
    }

    function updateTimer() {
        const elapsed = Math.floor((Date.now() - startTime) / 1000);
        const minutes = Math.floor(elapsed / 60);
        const seconds = elapsed % 60;
        $('#timer').text(`${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`);
    }

    // Autosave function
    function initAutosave() {
        autosaveInterval = setInterval(saveCurrentProgress, 30000); // Save every 30 seconds
    }

    // Map initialization
    function initializeMap() {
        if (!$('#assessmentMap').length) return;

        map = L.map('assessmentMap').setView([51.5074, -0.1278], 13);
        
        L.tileLayer(mapDrawingPublic.customMapUrl, {
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

    // Question loading and navigation
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
                loadMapQuestion(question);
                break;
            case 'mcq':
                loadMCQQuestion(question);
                break;
            case 'blanks':
                loadBlanksQuestion(question);
                break;
        }

        // Update navigation buttons
        updateNavigation(index);
        updateQuestionsList();
    }

    function loadMapQuestion(question) {
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
    }

    function loadMCQQuestion(question) {
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
    }

    function loadBlanksQuestion(question) {
        $('.map-wrapper').hide();
        $('#blanksContainer').show();
        
        // Split sentence by [BLANK] and create dropdowns
        const parts = question.content.sentence.split('[BLANK]');
        let sentenceHtml = parts[0];
        for (let i = 1; i < parts.length; i++) {
            sentenceHtml += `
                <select class="blank-dropdown" data-blank="${i-1}">
                    <option value="">${mapDrawingPublic.i18n.selectAnswer}</option>
                    ${question.content.options.map((option, j) => `
                        <option value="${j}">${option}</option>
                    `).join('')}
                </select>
            ` + parts[i];
        }
        $('.blanks-sentence').html(sentenceHtml);
    }

    function updateNavigation(index) {
        $('#prevButton').prop('disabled', index === 0);
        $('#nextButton').prop('disabled', index === questions.length - 1);
        $('#submitButton').toggle(index === questions.length - 1);
        $('#flagButton').toggleClass('active', flaggedQuestions.has(index));
        currentQuestion = index;
    }

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
            url: mapDrawingPublic.ajaxurl,
            type: 'POST',
            data: {
                action: 'save_drawing',
                nonce: mapDrawingPublic.nonce,
                question_id: question.id,
                drawing: answer,
                is_flagged: flaggedQuestions.has(currentQuestion)
            },
            success: function(response) {
                if (!response.success) {
                    console.error('Error saving progress:', response.data);
                }
            }
        });
    }

    // Event handlers
    function initEventHandlers() {
        // Drawing controls
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
            if (confirm(mapDrawingPublic.i18n.confirmClear)) {
                currentPath = [];
                redrawPath();
            }
        });

        // Map controls
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

        // Navigation
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

        // Question management
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

        // Submit assessment
        $('#submitButton').click(function() {
            if (!confirm(mapDrawingPublic.i18n.confirmSubmit)) {
                return;
            }

            saveCurrentProgress();

            $.ajax({
                url: mapDrawingPublic.ajaxurl,
                type: 'POST',
                data: {
                    action: 'submit_assessment',
                    nonce: mapDrawingPublic.nonce,
                    questions: questions,
                    time_taken: Math.floor((Date.now() - startTime) / 1000)
                },
                success: function(response) {
                    if (response.success) {
                        clearInterval(timerInterval);
                        clearInterval(autosaveInterval);
                        alert(mapDrawingPublic.i18n.submitSuccess);
                        location.reload();
                    } else {
                        alert(response.data || mapDrawingPublic.i18n.submitError);
                    }
                },
                error: function() {
                    alert(mapDrawingPublic.i18n.submitError);
                }
            });
        });

        // Modal handling
        $('.modal-close, .modal').click(function(e) {
            if (e.target === this) {
                $(this).closest('.modal').hide();
            }
        });

        // Question number clicks in review modal
        $(document).on('click', '.question-number', function() {
            const index = $(this).data('index');
            saveCurrentProgress();
            loadQuestion(index);
            $('#reviewModal').hide();
        });
    }

    // Update questions list in review modal
    function updateQuestionsList() {
        const html = questions.map((q, i) => `
            <button type="button" class="question-number ${i === currentQuestion ? 'current' : ''} 
                ${flaggedQuestions.has(i) ? 'flagged' : ''}" data-index="${i}">
                ${i + 1}
            </button>
        `).join('');
        $('#questionsList').html(html);
    }

    // Access code validation
    function validateAccessCode(code) {
        return $.ajax({
            url: mapDrawingPublic.ajaxurl,
            type: 'POST',
            data: {
                action: 'validate_access_code',
                nonce: mapDrawingPublic.nonce,
                access_code: code
            }
        });
    }

    // Initialize everything when document is ready
    $(document).ready(function() {
        // Check if we're on the assessment page
        if ($('#assessmentContainer').length) {
            initializeMap();
            initEventHandlers();

            // Handle access code form submission
            $('#accessCodeForm').on('submit', function(e) {
                e.preventDefault();
                
                validateAccessCode($('#accessCode').val())
                    .then(function(response) {
                        if (response.success) {
                            $('#accessCodeModal').hide();
                            $('#assessmentContainer').show();
                            initAssessment(mapDrawingPublic.questions);
                        } else {
                            alert(mapDrawingPublic.i18n.invalidAccessCode);
                        }
                    });
            });

            // Check existing access code
            const accessCode = getCookie('map_assessment_access_code');
            if (accessCode) {
                validateAccessCode(accessCode)
                    .then(function(response) {
                        if (response.success) {
                            $('#accessCodeModal').hide();
                            $('#assessmentContainer').show();
                            initAssessment(mapDrawingPublic.questions);
                        } else {
                            $('#accessCodeModal').show();
                        }
                    });
            } else {
                $('#accessCodeModal').show();
            }
        }
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

})(jQuery);