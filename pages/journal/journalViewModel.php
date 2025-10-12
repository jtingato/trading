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
        var $errorMsg = '';

        var $dataManager = null;

        // Simulated user ID (replace with session-based ID later)
        var $currentUser = 'user_123';

        public function __construct() {
            $this->dataManager = JournalDataManager::shared();
            $this->visibleFields = $this->dataManager->allVisibleColumnNames();
            $this->journalFields = $this->dataManager->getJournalFields();
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
            $submittedFields = $_POST['fields'] ?? [];

            // Converted POST[] into JournalField objects
            $fieldObjects = [];

            try {
                $orderingCount = 0;
                foreach ($submittedFields as $fieldId => $properties) {
                    
                    $thisField = $this->findJournalField($fieldId);

                    if($thisField === null) { continue; }
                    
                    $thisField->fieldName       = $fieldId;
                    $thisField->userId          = $this->currentUser;
                    $thisField->ordering        = ++$orderingCount; // ordering by array position
                    $thisField->friendlyName    = $properties['friendlyName'];
                    $thisField->isVisible       = isset($properties['isVisible']) && (int)$properties['isVisible'] === 1;
                } 
            } catch (Exception $e) {
                $this->errorMsg = "Failed to save field selection {$fieldId}: " . $e->getMessage();
            }
        

            try {
                $dataMan->saveVisibleJournalFields($this->currentUser, $this->journalFields);
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


