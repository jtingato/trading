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

    public function allColumns() {
        $headerQuery = "SELECT";
    }
}
?>