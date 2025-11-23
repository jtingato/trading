<?php 
    declare(strict_types=1);
?>

<table id="journalTable" class="journal-table table table-bordered">
    <thead>
        <tr id="sortable-header" class="active">
            <?php
            $columnHeaders = $viewModel->journalHeaderNames;
            $columnHeaderName = $viewModel->displayNamesFromFieldNames($columnHeaders);
            foreach ($columnHeaderName as $index => $columnName) {
                $safeId = preg_replace('/[^a-zA-Z0-9_-]/', '_', $columnName);
                echo "<th data-index=\"$index\" data-col=\"" . htmlspecialchars($safeId) . "\">";
                echo "<i class=\"fas fa-grip-vertical drag-icon\"></i> " . htmlspecialchars($columnName);
                echo "</th>";
            }
            ?>
        </tr>
    </thead>
    <tbody>
        <?php
        $rows = $viewModel->rows;
        if (!empty($rows)) {
            foreach ($rows as $row) {
                echo "<tr>";
                foreach ($columnHeaders as $columnName) {
                    $value = isset($row[$columnName]) ? $row[$columnName] : '';
                    echo "<td>" . htmlspecialchars(strval($value)) . "</td>";
                }
                echo "</tr>";
            }
        } else {
            $colspan = max(1, count($columnHeaders));
            echo "<tr><td colspan=\"" . $colspan . "\">No rows yet.</td></tr>";
        }
        ?>
    </tbody>
</table>

<!-- <a href="#" class="btn btn-info order">Get Column Order</a>
<p class="porder"></p> -->

<!-- SortableJS -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const headerRow = document.getElementById('sortable-header');
    const table = document.getElementById('journalTable');
    const tbody = table.querySelector('tbody');

    // Restore column order from localStorage
    const savedOrder = JSON.parse(localStorage.getItem('columnOrder') || 'null');
    if (savedOrder) {
        const headerCells = Array.from(headerRow.children);
        const reorderedHeader = savedOrder.map(i => headerCells[i]);
        reorderedHeader.forEach(cell => headerRow.appendChild(cell));

        Array.from(tbody.rows).forEach(row => {
            const cells = Array.from(row.cells);
            const reordered = savedOrder.map(i => cells[i]);
            reordered.forEach(cell => row.appendChild(cell));
        });

        // Update data-index attributes
        Array.from(headerRow.children).forEach((th, i) => {
            th.setAttribute('data-index', i);
        });
    }

    // Enable drag-and-drop
    new Sortable(headerRow, {
        animation: 150,
        ghostClass: 'sortable-ghost',
        handle: '.drag-icon',
        
        onEnd: function () {
        const newOrder = Array.from(headerRow.children).map(th => ({
            index: th.getAttribute('data-index'),
            label: th.textContent.trim()
        }));

        // Reorder each row's cells
        Array.from(tbody.rows).forEach(row => {
            const cells = Array.from(row.cells);
            const reordered = newOrder.map(obj => cells[parseInt(obj.index)]);
            reordered.forEach(cell => row.appendChild(cell));
        });

        // Update header data-index
        Array.from(headerRow.children).forEach((th, i) => {
            th.setAttribute('data-index', i);
        });

    // Send to server via AJAX
        fetch('/RequestHandler.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ columnOrder: newOrder.map(obj => obj.label) })
        })
        .then(response => response.json())
        .then(data => {
            console.log('Column order saved:', data);
        })
        .catch(error => {
            console.error('Error saving column order:', error);
        });
    }});

  // Show column order on button click
    document.querySelector('a.order').addEventListener('click', function (e) {
        e.preventDefault();
        const order = Array.from(headerRow.children).map(th => th.textContent.trim());
        document.querySelector('.porder').textContent = order.join(', ');
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
