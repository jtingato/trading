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
                echo "<th data-index=\"$index\" data-col=\"" . htmlspecialchars($safeId) . "\">" . htmlspecialchars($columnName) . "</th>";
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

<a href="#" class="btn btn-info order">Get Column Order</a>
<p class="porder"></p>

<!-- SortableJS -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const headerRow = document.getElementById('sortable-header');
  const table = document.getElementById('journalTable');
  const tbody = table.querySelector('tbody');

  new Sortable(headerRow, {
    animation: 150,
    ghostClass: 'sortable-ghost',
    onEnd: function () {
      const newOrder = Array.from(headerRow.children).map(th => th.getAttribute('data-index'));

      Array.from(tbody.rows).forEach(row => {
        const cells = Array.from(row.cells);
        const reordered = newOrder.map(i => cells[parseInt(i)]);
        reordered.forEach(cell => row.appendChild(cell));
      });

      // Update header data-index to reflect new order
      Array.from(headerRow.children).forEach((th, i) => {
        th.setAttribute('data-index', i);
      });
    }
  });

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
