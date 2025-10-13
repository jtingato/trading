<?php 
    declare(strict_types=1);
    require $navigationRouter->pathForFileNamed("JournalDataManager.php");
    
    class JournalViewModel {
    
        // All possible fields from the journal table
        /** @var JournalField[] */
        public array $journalFields = [];
        
        // The field that have been selected by the user to be displayed
        var $journalHeaderNames = [];
        
        // The journal entries from journal_table
        var $rows = [];

        // Error messages are save here.  Probably throe exceptions instead
        var $errorMsg = '';

        var $dataManager = null;

        // Simulated user ID (replace with session-based ID later)
        var $currentUser = 'user_123';

        public function __construct() {
            $this->dataManager = JournalDataManager::shared();
            $this->journalHeaderNames = $this->dataManager->visibleJournalFieldDisplayNames();
            $this->journalFields = $this->dataManager->getJournalFields();
        }
        
        function updateJournalEntries() {
            $dbManager = JournalDataManager::shared();
            // Finish this later
        }

        // Responds to user's selection of fields to display and saves to the db
        // Updates the values in the JournalFields[] from POST[] 
        function updateFieldSelections() {
             $dataMan = JournalDataManager::shared();

            // Check the POST array for html form dumping of selected fields
            if (!isset($_POST['fields']) || !is_array($_POST['fields'])) {
                $this->errorMsg = "No fields were selected.";
                return;
            }

            try {
                $orderingCount = 0;

                // // Array of field names from POST array
                $submittedFields = $_POST['fields'] ?? [];
                foreach ($submittedFields as $fieldId => $properties) {

                    // Find and acquire the field from JournalFields and update its values
                    $thisField = $this->findJournalField($fieldId);

                    if($thisField === null) { continue; }
                    
                    $thisField->fieldName       = $fieldId;
                    $thisField->userId          = $this->currentUser;
                    $thisField->ordering        = ++$orderingCount; // ordering by array position
                    $thisField->displayName    = $properties['displayName'];
                    $thisField->isVisible       = isset($properties['isVisible']) && (int)$properties['isVisible'] === 1;
                } 
            } catch (Exception $e) {
                $this->errorMsg = "Failed to save field selection {$fieldId}: " . $e->getMessage();
            }
        
            // Attempt to save to database and catch and PDO exception
            try {
                $dataMan->saveUpdatedJournalFields($this->currentUser, $this->journalFields);
            } catch (Throwable $e) {
                $this->errorMsg = "Failed to save field selections: " . get_class($e) . " - " . $e->getMessage();
                print($this->errorMsg);
            }
        }

        private function findJournalField(string $fieldId): ?JournalField {
            foreach ($this->journalFields as $field) {
                if ($field->fieldName === $fieldId) {
                    return $field;
                }
            }
            return null;
        }

    }
?>


