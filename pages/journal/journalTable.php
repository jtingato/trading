<?php 
    declare(strict_types=1);
    // $viewModel: is instantiated in Journal.php and this file is included in that file
?>

<table id="journalTable" class="journal-table">
    <thead>
        <tr>
            <?php
            $columnHeaderName = $viewModel->visibleFields;
            foreach ($columnHeaderName as $columnName) {
                echo "<th>" . htmlspecialchars($columnName) . "</th>";
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
                foreach ($columnHeaderName as $columnName) {
                    $value = isset($row[$columnName]) ? $row[$columnName] : '';
                    echo "<td>" . htmlspecialchars($value) . "</td>";
                }
                echo "</tr>";
            }
        } else {
            $colspan = max(1, count($columnHeaderName));
            echo "<tr><td colspan=\"" . $colspan . "\">No rows yet.</td></tr>";
        }
        ?>
    </tbody>
</table>