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

<script>
    document.addEventListener("DOMContentLoaded", function () {

        let startX, startWidth, currentTh;

        document.querySelectorAll("th .resize-handle").forEach(handle => {
            handle.addEventListener("mousedown", function (e) {
                currentTh = e.target.parentElement;
                startX = e.pageX;
                startWidth = currentTh.offsetWidth;
                document.addEventListener("mousemove", resizeColumn);
                document.addEventListener("mouseup", stopResize);
                e.preventDefault();
            });
        });

        function resizeColumn(e) {
            const newWidth = startWidth + (e.pageX - startX);
            currentTh.style.width = newWidth + "px";

            const index = Array.from(currentTh.parentNode.children).indexOf(currentTh);

            document.querySelectorAll("#journalTable tbody tr").forEach(row => {
                row.cells[index].style.width = newWidth + "px";
            });
        }

        function stopResize() {
            document.removeEventListener("mousemove", resizeColumn);
            document.removeEventListener("mouseup", stopResize);

            // Save the width to DB
            const fieldName = currentTh.getAttribute("data-field");

            fetch("/Http/RequestHandler.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    updateWidth: {
                        field: fieldName,
                        width: currentTh.offsetWidth
                    }
                })
            })
            .catch(err => console.error("Failed to save width:", err));
        }
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
