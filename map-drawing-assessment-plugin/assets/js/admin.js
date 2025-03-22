/**
 * Admin JavaScript for Map Drawing Assessment plugin
 */
(function($) {
    'use strict';

    // Initialize tooltips only if jQuery UI is available
    function initTooltips() {
        if (typeof $.fn.tooltip === 'function') {
            $('[title]').tooltip();
        }
    }

    // Generic AJAX handler with error handling
    function handleAjaxRequest(data, successCallback) {
        $.ajax({
            url: mapDrawingAdmin.ajaxurl,
            type: 'POST',
            data: {
                ...data,
                nonce: mapDrawingAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    if (successCallback) {
                        successCallback(response.data);
                    }
                } else {
                    alert(response.data || mapDrawingAdmin.i18n.errorMessage);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', status, error);
                alert(mapDrawingAdmin.i18n.errorMessage);
            }
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

            handleAjaxRequest({
                action: 'handle_bulk_action',
                bulk_action: action,
                question_ids: ids
            }, function() {
                location.reload();
            });
        });
    }

    // Handle form submissions
    function handleFormSubmissions() {
        // Map question form
        $('#mapQuestionForm').on('submit', function(e) {
            e.preventDefault();
            const formData = $(this).serializeArray();
            handleAjaxRequest({
                action: 'save_map_question',
                ...Object.fromEntries(formData)
            }, function() {
                location.reload();
            });
        });

        // MCQ form
        $('#mcqForm').on('submit', function(e) {
            e.preventDefault();
            const formData = $(this).serializeArray();
            handleAjaxRequest({
                action: 'save_mcq_question',
                ...Object.fromEntries(formData)
            }, function() {
                location.reload();
            });
        });

        // Fill blanks form
        $('#fillBlanksForm').on('submit', function(e) {
            e.preventDefault();
            const formData = $(this).serializeArray();
            handleAjaxRequest({
                action: 'save_blank_question',
                ...Object.fromEntries(formData)
            }, function() {
                location.reload();
            });
        });
    }

    // Handle user management actions
    function handleUserActions() {
        // Grant access
        $('.grant-access').on('click', function() {
            const userId = $(this).data('user-id');
            const accessPeriod = $('#accessPeriod').val();
            
            handleAjaxRequest({
                action: 'grant_user_access',
                user_id: userId,
                access_period: accessPeriod
            }, function() {
                location.reload();
            });
        });

        // Revoke access
        $('.revoke-access').on('click', function() {
            if (!confirm(mapDrawingAdmin.i18n.confirmRevoke)) return;
            
            const userId = $(this).data('user-id');
            handleAjaxRequest({
                action: 'revoke_user_access',
                user_id: userId
            }, function() {
                location.reload();
            });
        });
    }

    // Initialize all handlers when document is ready
    $(document).ready(function() {
        try {
            initTooltips();
            handleBulkActions();
            handleFormSubmissions();
            handleUserActions();
        } catch (error) {
            console.error('Initialization error:', error);
        }
    });

})(jQuery);