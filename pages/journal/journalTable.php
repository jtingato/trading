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
            ?>
                <th data-field="<?= htmlspecialchars($field) ?>" data-index="<?= $i ?>">
                    <i class="fas fa-grip-vertical drag-icon"></i>
                    <?= htmlspecialchars($label) ?>
                </th>
            <?php endforeach; ?>
        </tr>
    </thead>

    <tbody>
        <?php foreach ($viewModel->rows as $row): ?>
            <tr>
                <?php foreach ($fieldNames as $field): ?>
                    <td><?= htmlspecialchars((string)($row[$field] ?? '')) ?></td>
                <?php endforeach; ?>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>


<!-- SortableJS -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {

    const headerRow = document.getElementById("sortable-header");

    new Sortable(headerRow, {
        animation: 150,
        handle: ".drag-icon",
        ghostClass: "sortable-ghost",

        onEnd: function () {

            // Get new field order
            const ths = Array.from(headerRow.children);
            const newOrder = ths.map(th => th.getAttribute("data-field"));

            // Save locally
            localStorage.setItem("columnOrder", JSON.stringify(newOrder));

            // Save server-side
            fetch("/Http/RequestHandler.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ columnOrder: newOrder })
            })
            .then(() => {
                // ⭐ CRITICAL FIX: let PHP regenerate the table after ordering change
                window.location.reload();
            })
            .catch(err => console.error("Column order save failed:", err));
        }
    });

});
</script>

<style>
.sortable-ghost {
    opacity: 0.4;
    background-color: #ccc;
}
th {
    cursor: move;
}
</style>
