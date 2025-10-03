<table id="journalTable" class="journal-table">
    <thead>
        <tr>
            <?php
            $visibleCols = !empty($displayableFields) ? $displayableFields : $columns;
            foreach ($visibleCols as $col) {
                echo "<th>" . htmlspecialchars($col) . "</th>";
            }
            ?>
        </tr>
    </thead>
    <tbody>
        <?php
        if (!empty($rows)) {
            foreach ($rows as $row) {
                echo "<tr>";
                foreach ($visibleCols as $colName) {
                    $val = isset($row[$colName]) ? $row[$colName] : '';
                    echo "<td>" . htmlspecialchars($val) . "</td>";
                }
                echo "</tr>";
            }
        } else {
            $colspan = max(1, count($visibleCols));
            echo "<tr><td colspan=\"" . $colspan . "\">No rows yet.</td></tr>";
        }
        ?>
    </tbody>
</table>