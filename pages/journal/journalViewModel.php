<?php 
    declare(strict_types=1);
    require $navigationRouter->pathForFileNamed("JournalDataManager.php");
    
    class JournalViewModel {
    
        // All possible fields from the journal table
        /** @var JournalField[] */
        public array $journalFields = [];
        
        // The field that have been selected by the user to be displayed
        var $visibleFields = [];
        
        // The journal entries from journal_table
        var $rows = [];
        var $columns = ["One", "Two", "Three", "Four", "Five", "Six"];

        var $orderedColumns = [];
        var $errorMsg = '';

        var $dataManager = null;

        // Simulated user ID (replace with session-based ID later)
        var $currentUser = 'user_123';

        public function __construct() {
            $this->dataManager = JournalDataManager::shared();
            $this->visibleFields = $this->dataManager->allVisibleColumnNames();
            $this->journalFields = $this->dataManager->getJournalFields();
        } 

        function saveSelectedDisplayableFields() {
            print("saveSelectedDisplayableFields called");
        }

        function updateJournalEntries() {
            $dbManager = JournalDataManager::shared();
            // Finish this later
        }

        function updateFieldSelections() {
             $dataMan = JournalDataManager::shared();

            if (!isset($_POST['fields']) || !is_array($_POST['fields'])) {
                $this->errorMsg = "No fields were selected.";
                return;
            }

            // // Array of field names
            $selectedFields = $_POST['fields']; 

            try {
                $dataMan->saveVisibleJournalFields($this->currentUser, $selectedFields);
            } catch (Exception $e) {
                $this->errorMsg = "Failed to save field selections: " . $e->getMessage();
            }
        }
    }
?>


