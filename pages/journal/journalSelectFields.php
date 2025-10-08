<?php 
    declare(strict_types=1);
    $viewModel = new JournalViewModel();
?>

<h3>Select Displayable Fields</h3>

<form method="post" action="?page=journal&action=save_fields">
    <ul class='field-selection-list'>
        <?php foreach ($viewModel->journalFields as $field): ?>
            <li>
                <label>
                    <!-- Hidden input to ensure field is submitted even if unchecked -->
                    <input type="hidden" name="fields[<?php echo htmlspecialchars($field->fieldName); ?>]" value="0">
                    
                    <!-- Checkbox overrides hidden input if checked -->
                    <input type="checkbox" name="fields[<?php echo htmlspecialchars($field->fieldName); ?>]" value="1"
                        <?php echo $field->isVisible ? 'checked' : ''; ?>>
                    
                    <span class="field-label"><?php echo htmlspecialchars($field->friendlyName ?? $field->fieldName); ?></span>
                </label>
            </li>
        <?php endforeach; ?>
    </ul>
    <button type="submit">Save Selection</button>
</form>


