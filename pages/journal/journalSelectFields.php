<?php 
    declare(strict_types=1);
    // $viewModel: is instantiated in Journal.php and this file is included in that file
?>

<h3>Select Displayable Fields</h3>

<form method="post" action="?page=journal&action=save_fields">
    <ul class='field-selection-list'>
        <?php foreach ($viewModel->journalFields as $field): ?>
            <li>
                <label>
                    <!-- Hidden input for visibility fallback -->
                    <input type="hidden" name="fields[<?php echo htmlspecialchars($field->fieldName); ?>][isVisible]" value="0">

                    <!-- Checkbox for visibility -->
                    <input type="checkbox" name="fields[<?php echo htmlspecialchars($field->fieldName); ?>][isVisible]" value="1"
                        <?php echo $field->isVisible ? 'checked' : ''; ?>>

                    <!-- Editable display name -->
                    <input type="text" name="fields[<?php echo htmlspecialchars($field->fieldName); ?>][displayName]"
                        value="<?php echo htmlspecialchars($field->displayName ?? $field->fieldName); ?>"
                        class="display-name-input">

                    <span class="field-label">(<?php echo htmlspecialchars($field->fieldName); ?>)</span>
                </label>
            </li>
        <?php endforeach; ?>
    </ul>
    <button type="submit">Save Selection</button>
</form>


