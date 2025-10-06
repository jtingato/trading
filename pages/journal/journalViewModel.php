<?php 
    declare(strict_types=1);
    require $navigationRouter->pathForFileNamed("JournalDataManager.php");
    
    class JournalViewModel {
    
        // Allpossible fields from the journal table
        var $columns = ["One", "Two", "Three", "Four", "Five"];
        
        // The field that have been selected by the user to be displayed
        var $displayableFields = ["One", "Two", "Three", "Four", "Five"];
        
        // The journal entries from journal_table
        var $rows = [];

        var $orderedColumns = [];
        var $errorMsg = '';

        // Simulated user ID (replace with session-based ID later)
        var $currentUser = 'user_123';

        function saveSelectedDisplayableFields() {
            print("saveSelectedDisplayableFields called");
        }

        function updateJournalEntries() {
            $dbManager = JournalDataManager::shared();
            
            // // Connect to SQLite
            // try {
            //     if ($dbManager->tableExists('trading_journal')) {
            //         // Sync displayable fields and get column names
                
            //         // Handle saving selected fields
            //         if ($this->action === 'save_fields' && $_SERVER['REQUEST_METHOD'] === 'POST') {

            //             $this->saveSelectedDisplayableFields();

            //             // Redirect to journal view
            //             header("Location: ?page=journal");
            //             exit;
            //         }

            //         // Load displayable fields for select_fields view or journal view
            //         $rows = getDisplayableJournalFields($pdo, $currentUser);
            //     } else {
            //         $errorMsg = "Table <strong>trading_journal</strong> does not exist in the database.";
            //     }
            // } catch (Exception $e) {
            //     $errorMsg = "Error: " . htmlspecialchars($e->getMessage());
            // } catch (PDOException $e) {
            //     $errorMsg = "Database error: " . htmlspecialchars($e->getMessage());
            // }
        }
    }
?>


