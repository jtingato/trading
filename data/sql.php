<?php
function syncDisplayableFields($pdo) {
    // Step 1: Check if trading_journal table exists
    $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='trading_journal'");
    if (!$stmt) {
        throw new Exception("Failed to query sqlite_master.");
    }

    $tableExists = $stmt->fetchColumn();
    // Check if trading_journal table exists
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

function getFieldNames($pdo, $tableName) {
    return null;
}

function tableExists($name, $pdo) {
    return (bool) $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='{$name}'")->fetchColumn();
}

function getDisplayableJournalFields($pdo, $userId) {
    $stmt = $pdo->prepare("
            SELECT field_name FROM displayable_journal_fields
            WHERE user_id = :user_id
            ORDER BY field_name ASC
        ");
    $stmt->execute([':user_id' => $userId]);
    $displayableFields = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Load journal rows
    $stmt = $pdo->query("SELECT * FROM trading_journal ORDER BY exec_time DESC");

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}  

function insertSampleData($pdo) {
    $stmt = $pdo->prepare ("
    INSERT INTO trading_journal (order_id, symbol, asset_type, side, quantity, strike_price, pos_effect, price_per_unit, exec_time, option_expiration_date, broker, notes, strategy)
    VALUES
    (1001, 'AAPL', 'stock', 'buy', 50, NULL, NULL, 175.25, '2025-09-01 09:30:00', NULL, 'Schwab', 'Long-term hold', 'Growth'),

    (1002, 'TSLA', 'stock', 'sell', 20, NULL, NULL, 265.80, '2025-09-02 10:15:00', NULL, 'Schwab', 'Profit-taking', 'Momentum'),

    (1003, 'SPY_400C', 'option', 'buy', 10, 400.00, 'To Open', 5.50, '2025-09-03 11:00:00', '2025-10-18', 'IBKR', 'Bullish setup', 'Options swing'),

    (1004, 'BTCUSD', 'crypto', 'buy', 2, NULL, NULL, 27000.00, '2025-09-04 13:45:00', NULL, 'Coinbase', 'Crypto exposure', 'Diversification'),

    (1005, 'AMZN', 'stock', 'buy', 15, NULL, NULL, 135.40, '2025-09-05 14:20:00', NULL, 'Webull', 'Earnings play', 'Event-driven'),

    (1006, 'ES_F', 'future', 'sell', 1, 4450.00, 'To Close', 12.75, '2025-09-06 08:00:00', NULL, 'e-Trade', 'Closing short position', 'Futures scalping'),

    (1007, 'NVDA', 'stock', 'buy', 30, NULL, NULL, 420.10, '2025-09-07 09:35:00', NULL, 'Schwab', 'AI sector exposure', 'Sector rotation'),

    (1008, 'ETHUSD', 'crypto', 'sell', 1, NULL, NULL, 1650.00, '2025-09-08 16:00:00', NULL, 'Coinbase', 'Locking gains', 'Crypto swing'),

    (1009, 'QQQ_370P', 'option', 'sell', 5, 370.00, 'To Close', 3.25, '2025-09-09 12:30:00', '2025-10-18', 'IBKR', 'Closing bearish leg', 'Options hedge'),

    (1010, 'MSFT', 'stock', 'buy', 25, NULL, NULL, 310.75, '2025-09-10 10:00:00', NULL, 'Apex', 'Adding to core position', 'Long-term growth');
");
}