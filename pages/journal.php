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

<h2 style="display: flex; align-items: center; gap: 12px;">
    Trading Journal
    <button id="toggleMenu">
            <span class="icon">☰</span> 
            <span class="label">Menu</span>
        </button>
</h2>

<!-- Slide over Menu-->
<aside class="journal-menu">
            <ul>
                <li><a href="?page=journal&action=add_sample"><i class="fas fa-database"></i> Add Sample Data</a></li>
                <li><a href="?page=journal&action=add_new"><i class="fas fa-pen"></i> Add New Entry</a></li>
                <li><a href="?page=journal&action=select_fields"><i class="fas fa-sliders-h"></i> Select Fields</a></li>
            </ul>
        </aside>


<div class="journal-layout">
    
<script>
document.addEventListener('DOMContentLoaded', function () {
  const toggleBtn = document.getElementById('toggleMenu');
  const menu = document.querySelector('.journal-menu');

  toggleBtn.addEventListener('click', function () {
    menu.classList.toggle('open');
    toggleBtn.classList.toggle('open');
  });
});
</script>

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
