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

    function allColumns(): array {
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

    function allVisibleColumnNames(): array {
        // Get all visible fields from displayable_journal_fields
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

        $allColumns = $this->allColumns();
        foreach ($allColumns as $columnName) {
            if (in_array($columnName, $visibleFields, true)) {
                $visibleColumns[] = $columnName;
            }
        }

        return $visibleColumns;
    }

    function allColumnNames(): array {
        // Get all visible fields from displayable_journal_fields
        $visibleStmt = $this->pdo->prepare("
            SELECT field_name 
            FROM journal_fields 
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

        $allColumns = $this->allColumns();
        foreach ($allColumns as $columnName) {
            if (in_array($columnName, $visibleFields, true)) {
                $visibleColumns[] = $columnName;
            }
        }

        return $visibleColumns;
    }

    function allVisibleColumns(): array { 
        // Get all visible fields from displayable_journal_fields 
        $visibleStmt = $this->pdo->prepare(" SELECT * FROM journal_fields WHERE is_visible = 1 "); 
        $visibleStmt->execute(); 
        $visibleColumns = $visibleStmt->fetchAll();

        if (empty($visibleColumns)) { 
            print("Visible fields are empty"); 
        } 
        return $visibleColumns; 
    }

     public function getJournalFields(): array {
        $stmt = $this->pdo->prepare("
            SELECT field_name, user_id, ordering, friendly_name, is_visible
            FROM journal_fields
        ");
        $stmt->execute();

        $fields = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $fields[] = new JournalField(
                $row['field_name'],
                $row['user_id'],
                isset($row['ordering']) ? (int)$row['ordering'] : null,
                $row['friendly_name'] ?? null,
                (bool)$row['is_visible']
            );
        }
        return $fields;
    }

    public function saveVisibleJournalFields(string $userId, array $fields): void {        
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
                INSERT INTO journal_fields (field_name, user_id, friendly_name, ordering, is_visible)
                VALUES (:field_name, :user_id, :friendly_name, :ordering, :is_visible)
            ");

            $index = 0;
            foreach ($fields as $field) {
                $insertStmt->execute([
                ':field_name'       => $field->fieldName,
                ':user_id'          => $field->userId,
                ':friendly_name'    => $field->friendlyName,
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

    public function deleteAllJournalFieldsForUser(string $userId) {
        $stmt = $this->pdo->prepare("
            DELETE FROM journal_fields
            WHERE user_id = :user_id
        ");
        $stmt->execute([':user_id' => $userId]);
    }
}
?>