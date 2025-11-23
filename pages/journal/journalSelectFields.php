<?php 
    declare(strict_types=1);
?>

<div id="selectFieldsModal" class="modal-overlay" style="display:none;">
    <div class="modal-content">
        <h3>Select Displayable Fields</h3>

        <form method="post" action="?page=journal&action=save_fields">
            <ul class="field-selection-list">
                <?php foreach ($viewModel->journalFields as $field): ?>
                    <li>
                        <label>
                            <input type="hidden" name="fields[<?php echo htmlspecialchars($field->fieldName); ?>][isVisible]" value="0">
                            <input type="checkbox" name="fields[<?php echo htmlspecialchars($field->fieldName); ?>][isVisible]" value="1"
                                <?php echo $field->isVisible ? 'checked' : ''; ?>>
                            <input type="text" name="fields[<?php echo htmlspecialchars($field->fieldName); ?>][displayName]"
                                value="<?php echo htmlspecialchars($field->displayName ?? $field->fieldName); ?>"
                                class="display-name-input">
                            <span class="field-label">(<?php echo htmlspecialchars($field->fieldName); ?>)</span>
                        </label>
                    </li>
                <?php endforeach; ?>
            </ul>
            <button type="submit">Save Selection</button>
            <button type="button" id="closeModalBtn">Cancel</button>
        </form>
    </div>
</div>



