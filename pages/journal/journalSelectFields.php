<?php
    declare(strict_types=1);

    /** @var \Monarch\ViewModels\JournalViewModel $viewModel */
?>
<div id="selectFieldsModal" class="modal-overlay" style="display:none;">
    <div class="modal-content">
        <h3>Select Displayable Fields</h3>

        <form method="post" action="?page=journal&action=save_fields">
            <ul class="field-selection-list">
                <?php foreach ($viewModel->journalFields as $field): ?>
                    <li>
                        <label>
                            <!-- Hidden "0" ensures an unchecked box submits a value -->
                            <input type="hidden"
                                   name="fields[<?= htmlspecialchars($field->fieldName) ?>][isVisible]"
                                   value="0">

                            <input type="checkbox"
                                   name="fields[<?= htmlspecialchars($field->fieldName) ?>][isVisible]"
                                   value="1"
                                   <?= $field->isVisible ? 'checked' : '' ?>>

                            <input type="text"
                                   name="fields[<?= htmlspecialchars($field->fieldName) ?>][displayName]"
                                   value="<?= htmlspecialchars($field->displayName ?? $field->fieldName) ?>"
                                   class="display-name-input">

                            <span class="field-label">
                                (<?= htmlspecialchars($field->fieldName) ?>)
                            </span>
                        </label>
                    </li>
                <?php endforeach; ?>
            </ul>

            <button type="submit">Save Selection</button>
            <button type="button" id="closeModalBtn">Cancel</button>
        </form>
    </div>
</div>




