<?php
$navigationRouter = new NavigationRouter();

// Require sql.php 
require_once $navigationRouter->pathForFileNamed('sql.php');

// Path to the SQLite database
$dbPath = $navigationRouter->pathForFileNamed('monarch.db');

// Initialize variables
$rows = [];
$columns = [];
$orderedColumns = [];
$errorMsg = '';
$displayableFields = [];
$action = $_GET['action'] ?? '';

// Simulated user ID (replace with session-based ID later)
$currentUser = 'user_123';

// Connect to SQLite
try {
    $pdo = new PDO("sqlite:" . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (tableExists('trading_journal', $pdo)) {
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
        $rows = getDisplayableJournalFields($pdo, $currentUser);
    } else {
        $errorMsg = "Table <strong>trading_journal</strong> does not exist in the database.";
    }
} catch (Exception $e) {
    $errorMsg = "Error: " . htmlspecialchars($e->getMessage());
} catch (PDOException $e) {
    $errorMsg = "Database error: " . htmlspecialchars($e->getMessage());
}
?>

<h2>
    Trading Journal
</h2>

<div class="journal-menu-wrapper">
        <div class="journal-menu">
            <ul>
                <li><a href="?page=journal&action=add_new"><i class="fas fa-pen"></i> Add New Entry</a></li>
                <li><a href="?page=journal&action=import-csv"><i class="fas fa-database"></i> Import CSV</a></li>
                <li><a href="?page=journal&action=select_fields"><i class="fas fa-sliders-h"></i> Select Fields</a></li>
            </ul>
        </div>
    </div>

<div class="journal-layout">
    <!-- Right-hand journal content -->
    <div class="journal-container">
        <?php 
        if (!empty($errorMsg)):
            echo "<div class='error-box'> $errorMsg; ?></div>";
        endif;

        if ($action === 'select_fields'):
            include $navigationRouter->pathForFileNamed("journalSelectFields.php");
        else:
            include $navigationRouter->pathForFileNamed("journalTable.php");
        endif; 
    ?>
    </div>
</div>