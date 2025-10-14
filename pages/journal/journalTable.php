<?php 
    declare(strict_types=1);
    // $viewModel: is instantiated in Journal.php and this file is included in that file
?>

<table id="journalTable" class="journal-table">
    <thead>
        <tr>
            <?php
            $columnHeaders = $viewModel->journalHeaderNames;
            $columnHeaderName = $viewModel->displayNamesFromFieldNames($columnHeaders);
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
                foreach ($columnHeaders as $columnName) {
                    $value = isset($row[$columnName]) ? $row[$columnName] : '';
                    echo "<td>" . htmlspecialchars(strval($value)) . "</td>";
                }
                echo "</tr>";
            }
        } else {
            $colspan = max(1, count($columnHeades));
            echo "<tr><td colspan=\"" . $colspan . "\">No rows yet.</td></tr>";
        }
        ?>
    </tbody>
</table>