<?php
function syncDisplayableFields($pdo) {
    // Step 1: Check if trading_journal table exists
    $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='trading_journal'");
    if (!$stmt) {
        throw new Exception("Failed to query sqlite_master.");
    }

    $tableExists = $stmt->fetchColumn();
    if (!$tableExists) {
        throw new Exception("Table 'trading_journal' does not exist.");
    }

    // Step 2: Get column names
    $columns = [];
    $colStmt = $pdo->query("PRAGMA table_info(trading_journal)");
    if (!$colStmt) {
        throw new Exception("Failed to retrieve column info.");
    }

    $colInfo = $colStmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($colInfo as $c) {
        if (isset($c['name'])) {
            $columns[] = $c['name'];
        }
    }

    // Step 3: Create displayable_journal_fields table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS displayable_journal_fields (
            field_name TEXT PRIMARY KEY
        )
    ");

    // Step 4: Insert column names
    $insertStmt = $pdo->prepare("
        INSERT OR IGNORE INTO displayable_journal_fields (field_name) VALUES (:field_name)
    ");
    foreach ($columns as $colName) {
        $insertStmt->execute([':field_name' => $colName]);
    }

    return $columns;
}
