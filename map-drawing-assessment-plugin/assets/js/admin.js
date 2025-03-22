/**
 * Admin JavaScript for Map Drawing Assessment plugin
 */
(function($) {
    'use strict';

    // Initialize tooltips
    function initTooltips() {
        $('[title]').tooltip();
    }

    // Initialize datepickers
    function initDatepickers() {
        $('.datepicker').datepicker({
            dateFormat: 'yy-mm-dd',
            minDate: 0
        });
    }

    // Handle bulk actions
    function handleBulkActions() {
        $('.bulk-action-apply').on('click', function(e) {
            e.preventDefault();
            const action = $(this).siblings('select').val();
            const ids = $('.bulk-select:checked').map(function() {
                return $(this).val();
            }).get();

            if (!action || ids.length === 0) {
                alert(mapDrawingAdmin.i18n.selectItemsMessage);
                return;
            }

            if (!confirm(mapDrawingAdmin.i18n.confirmBulkAction)) {
                return;
            }

            $.ajax({
                url: mapDrawingAdmin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'handle_bulk_action',
                    nonce: mapDrawingAdmin.nonce,
                    bulk_action: action,
                    question_ids: ids
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert(response.data || mapDrawingAdmin.i18n.errorMessage);
                    }
                },
                error: function() {
                    alert(mapDrawingAdmin.i18n.errorMessage);
                }
            });
        });
    }

    // Handle map interactions for question creation/editing
    function initMapHandlers() {
        if (!$('#questionMap').length) return;

        let map = L.map('questionMap').setView([51.5074, -0.1278], 13);
        let startMarker = null;
        let endMarker = null;

        // Add custom map tiles
        L.tileLayer(mapDrawingAdmin.customMapUrl, {
            maxZoom: 19,
            attribution: '© SERU Map'
        }).addTo(map);

        // Custom markers
        const greenIcon = L.icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-green.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34]
        });

        const redIcon = L.icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34]
        });

        // Map click handler
        map.on('click', function(e) {
            if (!startMarker) {
                startMarker = L.marker(e.latlng, {icon: greenIcon, draggable: true})
                    .addTo(map)
                    .on('dragend', updateMarkerInputs);
                $('#startLat').val(e.latlng.lat.toFixed(6));
                $('#startLng').val(e.latlng.lng.toFixed(6));
            } else if (!endMarker) {
                endMarker = L.marker(e.latlng, {icon: redIcon, draggable: true})
                    .addTo(map)
                    .on('dragend', updateMarkerInputs);
                $('#endLat').val(e.latlng.lat.toFixed(6));
                $('#endLng').val(e.latlng.lng.toFixed(6));
            }
        });

        // Update coordinate inputs when markers are dragged
        function updateMarkerInputs() {
            if (startMarker) {
                const pos = startMarker.getLatLng();
                $('#startLat').val(pos.lat.toFixed(6));
                $('#startLng').val(pos.lng.toFixed(6));
            }
            if (endMarker) {
                const pos = endMarker.getLatLng();
                $('#endLat').val(pos.lat.toFixed(6));
                $('#endLng').val(pos.lng.toFixed(6));
            }
        }

        // Map controls
        $('#zoomIn').click(() => map.zoomIn());
        $('#zoomOut').click(() => map.zoomOut());
        $('#centerMap').click(() => map.setView([51.5074, -0.1278], 13));

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
        });
    }

    // Handle MCQ option management
    function initMCQHandlers() {
        // Add option
        $('.add-option').click(function() {
            const optionIndex = $('#optionsContainer .option-row').length;
            const newOption = `
                <div class="option-row">
                    <div class="option-input">
                        <input type="checkbox" name="correct_answers[]" value="${optionIndex}">
                        <input type="text" name="options[]" required placeholder="${mapDrawingAdmin.i18n.enterOptionText}">
                    </div>
                    <button type="button" class="button remove-option" title="${mapDrawingAdmin.i18n.removeOption}">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
            $('#optionsContainer').append(newOption);
        });

        // Remove option
        $(document).on('click', '.remove-option', function() {
            if ($('#optionsContainer .option-row').length > 1) {
                $(this).closest('.option-row').remove();
                // Update checkbox values
                $('#optionsContainer .option-row').each(function(index) {
                    $(this).find('input[type="checkbox"]').val(index);
                });
            } else {
                alert(mapDrawingAdmin.i18n.minimumOneOption);
            }
        });
    }

    // Handle fill in the blanks sentence building
    function initFillBlanksHandlers() {
        let blankCount = 0;

        // Add blank
        $(document).on('click', '.add-blank', function() {
            const blankMarker = `
                <div class="sentence-part">
                    <div class="blank-marker">
                        <span>Blank #${++blankCount}</span>
                        <button type="button" class="button remove-blank" title="${mapDrawingAdmin.i18n.removeBlank}">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <textarea class="sentence-text" rows="2" placeholder="${mapDrawingAdmin.i18n.enterText}"></textarea>
                    <button type="button" class="button add-blank" title="${mapDrawingAdmin.i18n.addBlank}">
                        <i class="fas fa-plus"></i> ${mapDrawingAdmin.i18n.addBlankHere}
                    </button>
                </div>
            `;
            $(this).closest('.sentence-part').after(blankMarker);
            updateAnswersContainer();
        });

        // Remove blank
        $(document).on('click', '.remove-blank', function() {
            $(this).closest('.sentence-part').remove();
            blankCount--;
            // Renumber remaining blanks
            $('.blank-marker span').each(function(index) {
                $(this).text(`Blank #${index + 1}`);
            });
            updateAnswersContainer();
        });

        // Update answers container
        function updateAnswersContainer() {
            const options = $('#optionsContainer input').map(function() {
                return $(this).val();
            }).get();

            let html = '';
            for (let i = 1; i <= blankCount; i++) {
                html += `
                    <div class="answer-row">
                        <span class="blank-number">${mapDrawingAdmin.i18n.blank} #${i}:</span>
                        <select name="correct_answers[]" required>
                            <option value="">${mapDrawingAdmin.i18n.selectCorrectAnswer}</option>
                            ${options.map((option, index) => `
                                <option value="${index}">${option || `${mapDrawingAdmin.i18n.option} ${index + 1}`}</option>
                            `).join('')}
                        </select>
                    </div>
                `;
            }
            $('#answersContainer').html(html);
        }
    }

    // Initialize all handlers when document is ready
    $(document).ready(function() {
        initTooltips();
        initDatepickers();
        handleBulkActions();
        initMapHandlers();
        initMCQHandlers();
        initFillBlanksHandlers();
    });

})(jQuery);