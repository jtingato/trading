<?php 
declare(strict_types=1);

// Get field names in saved order (from DB)
$fieldNames = $viewModel->journalHeaderNames;

// Convert to display names
$displayNames = $viewModel->displayNamesFromFieldNames($fieldNames);
?>

<table id="journalTable" class="journal-table table table-bordered">
    <thead>
        <tr id="sortable-header">
            <?php foreach ($displayNames as $i => $label): 
                $field = $fieldNames[$i];
                $width = $viewModel->findJournalField($field)->width;
                $widthStyle = $width ? "style=\"width: {$width}px\"" : "";
            ?>
                <th 
                    data-field="<?= htmlspecialchars($field) ?>" 
                    data-index="<?= $i ?>"
                    
                    <?= $widthStyle ?>
                >
                    <i class="fas fa-grip-vertical drag-icon"></i>
                    <?= htmlspecialchars($label) ?>
                    <div class="resize-handle"></div>
                </th>
            <?php endforeach; ?>
        </tr>
    </thead>

    <tbody>
        <?php foreach ($viewModel->rows as $row): ?>
            <tr data-row-id="<?= $row['id'] ?>">
                <?php foreach ($fieldNames as $field): ?>
                    <td 
                        data-field="<?= htmlspecialchars($field) ?>"
                        data-id="<?= htmlspecialchars((string)($row['id']), ENT_QUOTES) ?>"
                        data-has-dropdown="<?= isset($viewModel->dropdownFields[$field]) ? '1' : '0' ?>"
                        data-options="<?= isset($viewModel->dropdownFields[$field]) 
                            ? htmlspecialchars(json_encode($viewModel->dropdownFields[$field]), ENT_QUOTES) 
                            : '' ?>"
                    >
                        <div 
                            class="inline-editor"
                            contenteditable="<?= isset($viewModel->dropdownFields[$field]) ? 'false' : 'true' ?>"
                        >
                            <?= htmlspecialchars((string)($row[$field] ?? ''), ENT_QUOTES) ?>
                        </div>
                    </td>
                <?php endforeach; ?>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>


<!-- SortableJS -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script src="/pages/journal/js/journalTable.js"></script>

<style>
    .sortable-ghost { opacity: 0.4; background-color: #ccc; }
    th { cursor: move; }

    #journalTable td { transition: background-color 0.3s ease; }

    #journalTable td.saving {
        background-color: #fff4c4 !important;
    }

    #journalTable td.saved {
        background-color: #d4ffd6 !important;
    }

    #journalTable td.error {
        background-color: #ffd4d4 !important;
    }

    .inline-editor { width: 100%; display: block; }
    .inline-select { width: 100%; padding: 4px; }
</style>
