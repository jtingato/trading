<?php

require('DatabaseManager.php');

class JournalDataManager extends DatabaseManager {
    const JOURNAL_TABLE_NAME = "trading_journal";
    const DISPLAYABLE_FIELDS_TABLE_NAME = "displayable_journal_fields";

    private static ?JournalDataManager $instance = null;

    private function __construct() {
        parent::__construct("monarch.db");

        if (!$this->tableExists(self::JOURNAL_TABLE_NAME)) {
            throw new RuntimeException("Table '" . self::JOURNAL_TABLE_NAME . "' does not exist.");
        }
    }

    public static function shared(): JournalDataManager {
        if (self::$instance === null) {
            self::$instance = new JournalDataManager();
        }
        return self::$instance;
    }

    public function getConnection(): PDO {
        return $this->getPDO();
    }

    public function tableExists(string $name): bool {
        return parent::tableExists($name);
    }

    // Returns all of the table fields from trading_journal table
    function allJournalFieldNamess(): array {
        $stmt = $this->pdo->prepare("PRAGMA table_info(trading_journal)");
        $stmt->execute();
        $columns = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if (isset($row['name'])) {
                $columns[] = $row['name'];
            }
        }
        return $columns;
    }   

    /* Compares all fields in trading_journal against the entries the $visibleFields array,
     which was derived from journal_fidlds with a visible == true

     Return an array of only the fields that are in both - therefore only the fields deemeed visible by the user
    */
    public function visibleJournalFieldNames(): array {
        // Get all visible fields from journal_fields table
        $visibleStmt = $this->pdo->prepare("
            SELECT field_name 
            FROM journal_fields 
            WHERE is_visible = 1
        ");
        $visibleStmt->execute();
        $visibleFields = $visibleStmt->fetchAll(PDO::FETCH_COLUMN);

        if (empty($visibleFields)) {
            return [];
        }

        // Get all actual columns from trading_journal
        $columnsStmt = $this->pdo->prepare("PRAGMA table_info(trading_journal)");
        $columnsStmt->execute();
        $visibleColumns = [];

        $allColumns = $this->allJournalFieldNamess();
        foreach ($allColumns as $columnName) {
            if (in_array($columnName, $visibleFields, true)) {
                $visibleColumns[] = $columnName;
            }
        }

        return $visibleColumns;
    }

    public function visibleJournalFieldDisplayNames(): array {
        // Get all visible fields from journal_fields table
        $visibleStmt = $this->pdo->prepare("
            SELECT field_name 
            FROM journal_fields 
            WHERE is_visible = 1 
            ORDER BY ordering ASC
        ");
        $visibleStmt->execute();
        $visibleFields = $visibleStmt->fetchAll(PDO::FETCH_COLUMN);

        if (empty($visibleFields)) {
            return [];
        }

        return $visibleFields;
    }

    // Returns ALL JournalFields from the database's 'journal_field'  table
    public function getJournalFields(): array {
        $stmt = $this->pdo->prepare("
            SELECT field_name, user_id, ordering, display_name, is_visible
            FROM journal_fields
        ");
        $stmt->execute();

        $fields = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $fields[] = new JournalField(
                $row['field_name'],
                $row['user_id'],
                isset($row['ordering']) ? (int)$row['ordering'] : null,
                $row['display_name'] ?? null,
                (bool)$row['is_visible']
            );
        }
        return $fields;
    }

    // Return all Journal Entries from the db
    public function getJournalEntries($columnList = []): array {
        // Join the array into a comma-separated string
        $columns = $columnList ? implode(', ', $columnList) : '*';

        $stmt = $this->pdo->prepare("
            SELECT {$columns}
            FROM trading_journal
        ");
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }   

    // Updates the JournalFields with the newest values by deleting and replacing all fields in the db
    public function saveUpdatedJournalFields(string $userId, array $fields): void {        
        if (!$userId) {
            throw new RuntimeException("No user id has been provided");
        }

        if (empty($fields)) {
            throw new RuntimeException("No fields provided to saveVisibleJournalFields");
        }        

        $this->pdo->beginTransaction();
        try {
            // Delete all journal fields from db
            $this->deleteAllJournalFieldsForUser($userId);

            // Insert new selections
            $insertStmt = $this->pdo->prepare("
                INSERT INTO journal_fields (field_name, user_id, display_name, ordering, is_visible)
                VALUES (:field_name, :user_id, :display_name, :ordering, :is_visible)
            ");

            $index = 0;
            foreach ($fields as $field) {
                $insertStmt->execute([
                ':field_name'       => $field->fieldName,
                ':user_id'          => $field->userId,
                ':display_name'    => $field->displayName,
                ':ordering'         => $field->ordering,
                ':is_visible'       => $field->isVisible ? 1 : 0
                ]);
            }
            $this->pdo->commit(); 
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            throw new RuntimeException("Failed to save journal fields: " . $e->getMessage());
        }
    }

    // Deletes all JournalFields from the journal_fields table
    private function deleteAllJournalFieldsForUser(string $userId) {
        $stmt = $this->pdo->prepare("
            DELETE FROM journal_fields
            WHERE user_id = :user_id
        ");
        $stmt->execute([':user_id' => $userId]);
    }

    // Updates the database with the new column ordering.
    // $orderedColumnNames is an array of mixture of either fieldNames or display names in the new order
    // This function is primarily called from an AJAX request when the user reorders columns in the journal table.
    // Since the ajax returns the column headers which mat have been changed by the user, we need to map them back to field names.
    public function updateColumnOrdering(array $orderedColumnNames): void {
    $this->pdo->beginTransaction();
    try {
        $updateStmt = $this->pdo->prepare("
            UPDATE journal_fields
            SET ordering = :ordering
            WHERE field_name = :field_name
        ");

        foreach ($orderedColumnNames as $index => $displayName) {
            $fieldName = $this->fieldNameFromDisplayName($displayName);
            if ($fieldName === null) {
                throw new RuntimeException("No matching field_name found for displayName: {$displayName}");
            }

            $updateStmt->execute([
                ':ordering' => $index + 1,
                ':field_name' => $fieldName
            ]);
        }

        $this->pdo->commit();
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw new RuntimeException("Failed to update column ordering: " . $e->getMessage());
        }
    }

    private function fieldNameFromDisplayName(string $displayName): ?string {
        // First, check if displayName matches a field_name
        $stmt = $this->pdo->prepare("
            SELECT field_name
            FROM journal_fields
            WHERE field_name = :name
            LIMIT 1
        ");
        $stmt->execute([':name' => $displayName]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            return $result['field_name'];
        }

        // If not, check if displayName matches a display_name
        $stmt = $this->pdo->prepare("
            SELECT field_name
            FROM journal_fields
            WHERE display_name = :name
            LIMIT 1
        ");
        $stmt->execute([':name' => $displayName]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ? $result['field_name'] : null;
    }
}
?>