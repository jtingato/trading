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
            FROM visible_journal_fields 
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

    function allVisibleColumns(): array { 
        // Get all visible fields from displayable_journal_fields 
        $visibleStmt = $this->pdo->prepare(" SELECT * FROM visible_journal_fields WHERE is_visible = 1 "); 
        $visibleStmt->execute(); 
        $visibleColumns = $visibleStmt->fetchAll();

        if (empty($visibleColumns)) { 
            print("Visible fields are empty"); 
        } 
        return $visibleColumns; 
    }
}
?>