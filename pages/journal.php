<?php
require_once __DIR__ . '/../data/sql.php';

// Path to the SQLite database
$dbPath = __DIR__ . '/../data/monarch.db';

// Initialize variables
$rows = [];
$columns = [];
$errorMsg = '';
$displayableFields = [];
$action = $_GET['action'] ?? '';

// Simulated user ID (replace with session-based ID later)
$currentUser = 'user_123';

// Connect to SQLite
try {
    $pdo = new PDO("sqlite:" . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if trading_journal table exists
    $tableExists = (bool) $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='trading_journal'")->fetchColumn();

    if ($tableExists) {
        // Sync displayable fields and get column names
        $columns = syncDisplayableFields($pdo);

        // Handle saving selected fields
        if ($action === 'save_fields' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $selectedFields = $_POST['fields'] ?? [];

            // Clear existing selections for this user
            $deleteStmt = $pdo->prepare("DELETE FROM displayable_journal_fields WHERE user_id = :user_id");
            $deleteStmt->execute([':user_id' => $currentUser]);

            // Reinsert selected fields
            $insertStmt = $pdo->prepare("
                INSERT INTO displayable_journal_fields (user_id, field_name) VALUES (:user_id, :field_name)
            ");
            foreach ($selectedFields as $field) {
                $insertStmt->execute([
                    ':user_id' => $currentUser,
                    ':field_name' => $field
                ]);
            }

            // Redirect to journal view
            header("Location: ?page=journal");
            exit;
        }

        // Load displayable fields for select_fields view or journal view
        $stmt = $pdo->prepare("
            SELECT field_name FROM displayable_journal_fields
            WHERE user_id = :user_id
            ORDER BY field_name ASC
        ");
        $stmt->execute([':user_id' => $currentUser]);
        $displayableFields = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Load journal rows
        $stmt = $pdo->query("SELECT * FROM trading_journal ORDER BY exec_time DESC");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $errorMsg = "Table <strong>trading_journal</strong> does not exist in the database.";
    }
} catch (Exception $e) {
    $errorMsg = "Error: " . htmlspecialchars($e->getMessage());
} catch (PDOException $e) {
    $errorMsg = "Database error: " . htmlspecialchars($e->getMessage());
}
?>

<style>
.journal-layout { display:flex; gap:20px; align-items:flex-start; margin-top:10px; }
.journal-menu { width:200px; background:#f8f9fa; border:1px solid #ddd; border-radius:8px; padding:15px; box-sizing:border-box; }
.journal-menu ul { list-style:none; padding:0; margin:0; }
.journal-menu li { margin-bottom:12px; }
.journal-menu a { text-decoration:none; color:#333; font-weight:600; display:block; padding:8px; border-radius:6px; transition:background .15s; }
.journal-menu a:hover { background:#e9ecef; }
.journal-container { flex:1; overflow-x:auto; }
.error-box { color:#a00; background:#fff0f0; padding:10px; border:1px solid #f5c2c2; border-radius:6px; margin-bottom:12px; }
.journal-table { width:100%; border-collapse:collapse; }
.journal-table th, .journal-table td { border:1px solid #ddd; padding:8px; text-align:left; }
.journal-table th { background:#f4f6f8; }
</style>

<h2>Trading Journal</h2>

<div class="journal-layout">
    <!-- Left-hand menu -->
    <aside class="journal-menu">
        <ul>
            <li><a href="?page=journal&action=add_sample">➕ Add Sample Data</a></li>
            <li><a href="?page=journal&action=add_new">📝 Add New Entry</a></li>
            <li><a href="?page=journal&action=select_fields">⚙️ Select Fields</a></li>
            <li><a href="logout.php">🚪 Logout</a></li>
        </ul>
    </aside>

    <!-- Right-hand journal content -->
    <div class="journal-container">
        <?php if (!empty($errorMsg)): ?>
            <div class="error-box"><?php echo $errorMsg; ?></div>
        <?php endif; ?>

        <?php if ($action === 'select_fields'): ?>
            <h3>Select Displayable Fields</h3>
            <form method="post" action="?page=journal&action=save_fields">
                <ul style="list-style:none; padding-left:0;">
                    <?php foreach ($columns as $field): ?>
                        <li>
                            <label>
                                <input type="checkbox" name="fields[]" value="<?php echo htmlspecialchars($field); ?>"
                                    <?php echo in_array($field, $displayableFields) ? 'checked' : ''; ?>>
                                <?php echo htmlspecialchars($field); ?>
                            </label>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <button type="submit">Save Selection</button>
            </form>
        <?php else: ?>
            <table class="journal-table">
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
        <?php endif; ?>
    </div>
</div>
