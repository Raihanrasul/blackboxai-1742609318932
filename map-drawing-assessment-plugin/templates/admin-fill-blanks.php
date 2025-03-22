<?php
// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}
?>
<div class="wrap">
    <h1 class="wp-heading-inline"><?php _e('Create Fill in the Blanks Question', 'map-drawing-assessment'); ?></h1>
    <hr class="wp-header-end">

    <div class="fill-blanks-container">
        <div class="postbox">
            <div class="inside">
                <form id="fillBlanksForm">
                    <?php wp_nonce_field('map_drawing_admin_nonce', 'nonce'); ?>

                    <div class="form-field">
                        <label for="questionTitle"><?php _e('Question Title', 'map-drawing-assessment'); ?> <span class="required">*</span></label>
                        <input type="text" id="questionTitle" name="title" required>
                        <p class="description"><?php _e('Enter a descriptive title for the question.', 'map-drawing-assessment'); ?></p>
                    </div>

                    <div class="form-field">
                        <label><?php _e('Sentence with Blanks', 'map-drawing-assessment'); ?> <span class="required">*</span></label>
                        <div class="sentence-builder">
                            <div id="sentenceContainer">
                                <div class="sentence-part">
                                    <textarea class="sentence-text" rows="2" placeholder="<?php _e('Enter text...', 'map-drawing-assessment'); ?>"></textarea>
                                    <button type="button" class="button add-blank" title="<?php _e('Add Blank', 'map-drawing-assessment'); ?>">
                                        <i class="fas fa-plus"></i> <?php _e('Add Blank Here', 'map-drawing-assessment'); ?>
                                    </button>
                                </div>
                            </div>
                            <input type="hidden" name="sentence" id="completeSentence">
                        </div>
                        <p class="description"><?php _e('Build your sentence and add blanks where needed. Each blank will be replaced with a dropdown containing the options below.', 'map-drawing-assessment'); ?></p>
                    </div>

                    <div class="form-field">
                        <label><?php _e('Options for Blanks', 'map-drawing-assessment'); ?> <span class="required">*</span></label>
                        <p class="info-text"><?php _e('Define exactly 6 options that will be available for all blanks. Users cannot reuse an option within the same question.', 'map-drawing-assessment'); ?></p>
                        
                        <div id="optionsContainer">
                            <?php for ($i = 0; $i < 6; $i++): ?>
                            <div class="option-row">
                                <span class="option-number"><?php echo $i + 1; ?></span>
                                <input type="text" name="options[]" required placeholder="<?php _e('Enter option text', 'map-drawing-assessment'); ?>">
                            </div>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <div class="form-field">
                        <label><?php _e('Correct Answers', 'map-drawing-assessment'); ?> <span class="required">*</span></label>
                        <div id="answersContainer">
                            <!-- Dynamically populated based on blanks -->
                        </div>
                        <p class="description"><?php _e('Select the correct option for each blank. The order matters and must match the blanks in the sentence.', 'map-drawing-assessment'); ?></p>
                    </div>

                    <div class="form-field">
                        <label><?php _e('Scoring', 'map-drawing-assessment'); ?></label>
                        <div class="scoring-info">
                            <i class="fas fa-info-circle"></i>
                            <div>
                                <p><?php _e('Scoring Rules:', 'map-drawing-assessment'); ?></p>
                                <ul>
                                    <li><?php _e('All blanks correct = 2 points', 'map-drawing-assessment'); ?></li>
                                    <li><?php _e('Any blank wrong or repeated = 0 points', 'map-drawing-assessment'); ?></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="button button-primary">
                            <i class="fas fa-save"></i> <?php _e('Save Question', 'map-drawing-assessment'); ?>
                        </button>
                        <button type="button" class="button" id="previewQuestion">
                            <i class="fas fa-eye"></i> <?php _e('Preview', 'map-drawing-assessment'); ?>
                        </button>
                        <button type="button" class="button" id="clearForm">
                            <i class="fas fa-undo"></i> <?php _e('Clear Form', 'map-drawing-assessment'); ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Preview Modal -->
        <div id="previewModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2><?php _e('Question Preview', 'map-drawing-assessment'); ?></h2>
                    <button class="modal-close"><i class="fas fa-times"></i></button>
                </div>
                <div class="modal-body">
                    <div id="previewContent"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.fill-blanks-container {
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

.sentence-builder {
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 15px;
    background: #f8f9fa;
}

.sentence-part {
    display: flex;
    gap: 10px;
    align-items: flex-start;
    margin-bottom: 10px;
}

.sentence-text {
    flex-grow: 1;
    resize: vertical;
}

.blank-marker {
    background: #e9ecef;
    padding: 8px 15px;
    border-radius: 4px;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    margin: 0 5px;
}

.option-row {
    display: flex;
    gap: 10px;
    align-items: center;
    margin-bottom: 10px;
}

.option-number {
    width: 24px;
    height: 24px;
    background: #007cba;
    color: #fff;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: bold;
}

.answer-row {
    display: flex;
    gap: 10px;
    align-items: center;
    margin-bottom: 10px;
    background: #f8f9fa;
    padding: 10px;
    border-radius: 4px;
}

.blank-number {
    font-weight: bold;
    min-width: 80px;
}

.scoring-info {
    display: flex;
    gap: 15px;
    background: #f0f6fc;
    padding: 15px;
    border-radius: 4px;
    margin-top: 10px;
}

.scoring-info i {
    color: #007cba;
    font-size: 20px;
    margin-top: 3px;
}

.scoring-info ul {
    margin: 5px 0 0 20px;
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

.info-text {
    color: #007cba;
    font-style: italic;
    margin: 5px 0 15px;
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
    max-width: 700px;
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
</style>

<script>
jQuery(document).ready(function($) {
    let blankCount = 0;

    // Add blank
    $(document).on('click', '.add-blank', function() {
        const blankMarker = `
            <div class="sentence-part">
                <div class="blank-marker">
                    <span>Blank #${++blankCount}</span>
                    <button type="button" class="button remove-blank" title="<?php _e('Remove Blank', 'map-drawing-assessment'); ?>">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <textarea class="sentence-text" rows="2" placeholder="<?php _e('Enter text...', 'map-drawing-assessment'); ?>"></textarea>
                <button type="button" class="button add-blank" title="<?php _e('Add Blank', 'map-drawing-assessment'); ?>">
                    <i class="fas fa-plus"></i> <?php _e('Add Blank Here', 'map-drawing-assessment'); ?>
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
                    <span class="blank-number"><?php _e('Blank', 'map-drawing-assessment'); ?> #${i}:</span>
                    <select name="correct_answers[]" required>
                        <option value=""><?php _e('Select correct answer', 'map-drawing-assessment'); ?></option>
                        ${options.map((option, index) => `
                            <option value="${index}">${option || `<?php _e('Option', 'map-drawing-assessment'); ?> ${index + 1}`}</option>
                        `).join('')}
                    </select>
                </div>
            `;
        }
        $('#answersContainer').html(html);
    }

    // Update complete sentence
    function updateCompleteSentence() {
        let sentence = '';
        $('.sentence-text').each(function(index) {
            sentence += $(this).val();
            if ($(this).closest('.sentence-part').next().find('.blank-marker').length) {
                sentence += '[BLANK]';
            }
        });
        $('#completeSentence').val(sentence);
    }

    // Preview question
    $('#previewQuestion').click(function() {
        updateCompleteSentence();
        const sentence = $('#completeSentence').val();
        const options = $('#optionsContainer input').map(function() {
            return $(this).val();
        }).get();

        let previewHtml = `
            <div class="preview-question">
                <p>${sentence.replace(/\[BLANK\]/g, '<select disabled><option>_____</option></select>')}</p>
                <div class="preview-options">
                    <p><strong><?php _e('Available Options:', 'map-drawing-assessment'); ?></strong></p>
                    <ul>
                        ${options.map(option => `<li>${option}</li>`).join('')}
                    </ul>
                </div>
            </div>
        `;

        $('#previewContent').html(previewHtml);
        $('#previewModal').show();
    });

    // Close modal
    $('.modal-close, .modal').click(function(e) {
        if (e.target === this) {
            $('#previewModal').hide();
        }
    });

    // Clear form
    $('#clearForm').click(function() {
        if (confirm('<?php _e('Are you sure you want to clear the form?', 'map-drawing-assessment'); ?>')) {
            $('#fillBlanksForm')[0].reset();
            $('#sentenceContainer').html(`
                <div class="sentence-part">
                    <textarea class="sentence-text" rows="2" placeholder="<?php _e('Enter text...', 'map-drawing-assessment'); ?>"></textarea>
                    <button type="button" class="button add-blank" title="<?php _e('Add Blank', 'map-drawing-assessment'); ?>">
                        <i class="fas fa-plus"></i> <?php _e('Add Blank Here', 'map-drawing-assessment'); ?>
                    </button>
                </div>
            `);
            blankCount = 0;
            updateAnswersContainer();
        }
    });

    // Form submission
    $('#fillBlanksForm').on('submit', function(e) {
        e.preventDefault();
        updateCompleteSentence();

        if (blankCount === 0) {
            alert('<?php _e('Please add at least one blank to the sentence.', 'map-drawing-assessment'); ?>');
            return;
        }

        const options = $('#optionsContainer input').map(function() {
            return $(this).val();
        }).get();

        if (options.some(option => !option)) {
            alert('<?php _e('Please fill in all six options.', 'map-drawing-assessment'); ?>');
            return;
        }

        var formData = $(this).serializeArray();
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'save_blank_question',
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