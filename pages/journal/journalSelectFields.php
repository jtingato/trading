<?php 
    declare(strict_types=1);
    $viewModel = new JournalViewModel();
?>

<h3>Select Displayable Fields</h3>
    <form method="post" action="?page=journal&action=save_fields">
        <ul class='field-selection-list'>
            <?php foreach ($viewModel->visibleFields as $field): ?>
                <li>
                    <label>
                        <input type="checkbox" name="fields[]" value="<?php echo htmlspecialchars($field); ?>"
                            <?php echo in_array($field, $viewModel->visibleFields) ? 'checked' : ''; ?>>
                            <span class="field-label"><?php echo htmlspecialchars($field); ?></span>
                    </label>
                </li>
            <?php endforeach; ?>
        </ul>
        <button type="submit">Save Selection</button>
    </form>

