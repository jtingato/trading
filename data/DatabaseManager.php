<?php

declare(strict_types=1);

abstract class DatabaseManager {
    private $navRouter;

    public readonly string $dbname;
    protected ?PDO $pdo = null;

    public function __construct($dbname) {
        $this->dbname = $dbname;
        $this->navRouter = new NavigationRouter();
        $this->getPDO();
    }

    protected function getPDO(): PDO {
        if ($this->pdo !== null) { return $this->pdo; }

        // Check for the existance of this file at the given file path
        $dbPath = $this->navRouter->pathForFileNamed($this->dbname);
        if (!file_exists($dbPath)) {
            throw new RuntimeException("Database file not found: $dbPath");
        }

        // Set the "Php Data Object" connection
        $this->pdo = new PDO("sqlite:" . $dbPath);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $this->pdo;
    }

    protected function tableExists(string $name): bool {
        $stmt = $this->getPDO()->query(
            "SELECT name FROM sqlite_master WHERE type='table' AND name='{$name}'"
        );
        return (bool) $stmt->fetchColumn();
    }
}