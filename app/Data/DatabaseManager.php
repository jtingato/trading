<?php
declare(strict_types=1);

namespace Monarch\Data;

use PDO;
use RuntimeException;

class DatabaseManager
{
    public readonly string $dbname;
    protected ?PDO $pdo = null;

    /**
     * @param string $dbname  SQLite database file name (e.g., "monarch.db")
     */
    public function __construct(string $dbname)
    {
        $this->dbname = $dbname;
        $this->getPDO(); // initialize connection immediately
    }

    /**
     * Returns a configured PDO instance.
     */
    protected function getPDO(): PDO
    {
        if ($this->pdo !== null) {
            return $this->pdo;
        }

        // Database should live in the project root under /data/
        // Adjust this path if your file lives somewhere else
        $dbPath = __DIR__ . '/' . $this->dbname;

        if (!is_file($dbPath)) {
            throw new RuntimeException("Database file not found: {$dbPath}");
        }

        $pdo = new PDO('sqlite:' . $dbPath);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->pdo = $pdo;
        return $pdo;
    }

    /**
     * Checks if a database table exists.
     */
    protected function tableExists(string $name): bool
    {
        $stmt = $this->getPDO()->prepare(
            "SELECT name FROM sqlite_master WHERE type = 'table' AND name = :name"
        );
        $stmt->execute(['name' => $name]);

        return (bool) $stmt->fetchColumn();
    }

    /**
     * Executes arbitrary SQL. Returns the PDOStatement.
     */
    public function executeSql(string $sql): \PDOStatement
    {
        return $this->getPDO()->query($sql);
    }
}
