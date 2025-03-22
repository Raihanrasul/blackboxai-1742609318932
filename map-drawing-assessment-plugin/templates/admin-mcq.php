<?php
// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}
?>
<div class="wrap">
    <h1 class="wp-heading-inline"><?php _e('Create Multiple Choice Question', 'map-drawing-assessment'); ?></h1>
    <hr class="wp-header-end">

    <div class="mcq-container">
        <div class="postbox">
            <div class="inside">
                <form id="mcqForm">
                    <?php wp_nonce_field('map_drawing_admin_nonce', 'nonce'); ?>

                    <div class="form-field">
                        <label for="questionTitle"><?php _e('Question Title', 'map-drawing-assessment'); ?> <span class="required">*</span></label>
                        <input type="text" id="questionTitle" name="title" required>
                        <p class="description"><?php _e('Enter a descriptive title for the question.', 'map-drawing-assessment'); ?></p>
                    </div>

                    <div class="form-field">
                        <label for="questionText"><?php _e('Question Text', 'map-drawing-assessment'); ?> <span class="required">*</span></label>
                        <textarea id="questionText" name="question" rows="4" required></textarea>
                        <p class="description"><?php _e('Enter the question text.', 'map-drawing-assessment'); ?></p>
                    </div>

                    <div class="form-field">
                        <label><?php _e('Points', 'map-drawing-assessment'); ?> <span class="required">*</span></label>
                        <div class="points-selector">
                            <label class="point-option">
                                <input type="radio" name="points" value="1" required>
                                <span>1 <?php _e('Point', 'map-drawing-assessment'); ?></span>
                            </label>
                            <label class="point-option">
                                <input type="radio" name="points" value="2">
                                <span>2 <?php _e('Points', 'map-drawing-assessment'); ?></span>
                            </label>
                            <label class="point-option">
                                <input type="radio" name="points" value="3">
                                <span>3 <?php _e('Points', 'map-drawing-assessment'); ?></span>
                            </label>
                        </div>
                    </div>

                    <div class="form-field">
                        <label><?php _e('Answer Options', 'map-drawing-assessment'); ?> <span class="required">*</span></label>
                        <div id="optionsContainer">
                            <div class="option-row">
                                <div class="option-input">
                                    <input type="checkbox" name="correct_answers[]" value="0">
                                    <input type="text" name="options[]" required placeholder="<?php _e('Enter option text', 'map-drawing-assessment'); ?>">
                                </div>
                                <button type="button" class="button remove-option" title="<?php _e('Remove Option', 'map-drawing-assessment'); ?>">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <button type="button" class="button add-option">
                            <i class="fas fa-plus"></i> <?php _e('Add Option', 'map-drawing-assessment'); ?>
                        </button>
                        <p class="description"><?php _e('Add options and check the correct answer(s). Multiple correct answers are allowed.', 'map-drawing-assessment'); ?></p>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="button button-primary">
                            <i class="fas fa-save"></i> <?php _e('Save Question', 'map-drawing-assessment'); ?>
                        </button>
                        <button type="button" class="button" id="clearForm">
                            <i class="fas fa-undo"></i> <?php _e('Clear Form', 'map-drawing-assessment'); ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.mcq-container {
    max-width: 800px;
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

.points-selector {
    display: flex;
    gap: 15px;
    margin-top: 10px;
}

.point-option {
    display: flex;
    align-items: center;
    gap: 5px;
    padding: 8px 15px;
    border: 1px solid #ddd;
    border-radius: 4px;
    cursor: pointer;
}

.point-option:hover {
    background: #f0f0f1;
}

.point-option input[type="radio"] {
    margin: 0;
}

.option-row {
    display: flex;
    gap: 10px;
    align-items: center;
    margin-bottom: 10px;
}

.option-input {
    flex-grow: 1;
    display: flex;
    align-items: center;
    gap: 10px;
    background: #f8f9fa;
    padding: 10px;
    border-radius: 4px;
}

.option-input input[type="checkbox"] {
    margin: 0;
}

.option-input input[type="text"] {
    flex-grow: 1;
}

.remove-option {
    padding: 0 !important;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #d63638;
}

.add-option {
    margin-top: 10px;
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
    // Add option row
    $('.add-option').click(function() {
        var optionIndex = $('#optionsContainer .option-row').length;
        var newOption = `
            <div class="option-row">
                <div class="option-input">
                    <input type="checkbox" name="correct_answers[]" value="${optionIndex}">
                    <input type="text" name="options[]" required placeholder="<?php _e('Enter option text', 'map-drawing-assessment'); ?>">
                </div>
                <button type="button" class="button remove-option" title="<?php _e('Remove Option', 'map-drawing-assessment'); ?>">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        $('#optionsContainer').append(newOption);
    });

    // Remove option row
    $(document).on('click', '.remove-option', function() {
        if ($('#optionsContainer .option-row').length > 1) {
            $(this).closest('.option-row').remove();
            // Update checkbox values
            $('#optionsContainer .option-row').each(function(index) {
                $(this).find('input[type="checkbox"]').val(index);
            });
        } else {
            alert('<?php _e('At least one option is required.', 'map-drawing-assessment'); ?>');
        }
    });

    // Clear form
    $('#clearForm').click(function() {
        if (confirm('<?php _e('Are you sure you want to clear the form?', 'map-drawing-assessment'); ?>')) {
            $('#mcqForm')[0].reset();
            $('#optionsContainer').html(`
                <div class="option-row">
                    <div class="option-input">
                        <input type="checkbox" name="correct_answers[]" value="0">
                        <input type="text" name="options[]" required placeholder="<?php _e('Enter option text', 'map-drawing-assessment'); ?>">
                    </div>
                    <button type="button" class="button remove-option" title="<?php _e('Remove Option', 'map-drawing-assessment'); ?>">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `);
        }
    });

    // Form submission
    $('#mcqForm').on('submit', function(e) {
        e.preventDefault();

        // Validate at least one correct answer is selected
        if (!$('input[name="correct_answers[]"]:checked').length) {
            alert('<?php _e('Please select at least one correct answer.', 'map-drawing-assessment'); ?>');
            return;
        }

        var formData = $(this).serializeArray();
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'save_mcq_question',
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