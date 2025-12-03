<?php
declare(strict_types=1);

namespace Monarch\Data;

use Monarch\Data\Models\JournalField;
use PDO;
use PDOException;
use RuntimeException;
use Throwable;
use DateTime;

class JournalDataManager extends DatabaseManager
{
    public const JOURNAL_TABLE_NAME            = 'trading_journal';
    public const DISPLAYABLE_FIELDS_TABLE_NAME = 'journal_fields';

    private static ?JournalDataManager $instance = null;

    private function __construct()
    {
        parent::__construct('monarch.db');

        if (!$this->tableExists(self::JOURNAL_TABLE_NAME)) {
            throw new RuntimeException(
                "Table '" . self::JOURNAL_TABLE_NAME . "' does not exist."
            );
        }
    }

    public static function shared(): JournalDataManager
    {
        if (self::$instance === null) {
            self::$instance = new JournalDataManager();
        }
        return self::$instance;
    }

    public function getConnection(): PDO
    {
        return $this->getPDO();
    }

    /**
     * Returns an array of *field names* in the order determined by the DB.
     */
    public function visibleJournalFieldNames(): array
    {
        $stmt = $this->pdo->prepare("
            SELECT field_name
            FROM journal_fields
            WHERE is_visible = 1
            ORDER BY ordering ASC
        ");

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Return all field names in trading_journal via PRAGMA.
     */
    public function allJournalFieldNames(): array
    {
        $stmt = $this->pdo->query("PRAGMA table_info(trading_journal)");

        $cols = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if (!empty($row['name'])) {
                $cols[] = $row['name'];
            }
        }
        return $cols;
    }

    /**
     * Return ALL JournalField objects from journal_fields table.
     */
    public function getJournalFields(): array
    {
        $stmt = $this->pdo->prepare("
            SELECT field_name, user_id, ordering, display_name, is_visible, width
            FROM journal_fields
            ORDER BY ordering ASC
        ");
        $stmt->execute();

        $result = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[] = new JournalField(
                $row['field_name'],
                $row['user_id'],
                isset($row['ordering']) ? (int)$row['ordering'] : null,
                $row['display_name'] ?: null,
                (bool)$row['is_visible'],
                isset($row['width']) ? (int)$row['width'] : null
            );
        }

        return $result;
    }

    /**
     * Returns rows from trading_journal in the specified column order.
     */
    public function getJournalEntries(array $columnList = []): array
    {
        // Always include the primary key
        if (!in_array('id', $columnList, true)) {
            $columnList = array_merge(['id'], $columnList);
        }
        
        $columns = $columnList ? implode(', ', $columnList) : '*';

        $stmt = $this->pdo->query("SELECT {$columns} FROM trading_journal");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Format exec_time
        foreach ($rows as &$entry) {
            if (!empty($entry['exec_time'])) {
                $date = new DateTime($entry['exec_time']);
                $entry['exec_time'] =
                    strtoupper($date->format('M')) . ' ' .
                    $date->format('d, Y - h:i a');
            }
        }

        return $rows;
    }

    /**
     * Save updated visibility + display names + ordering for all fields of a user.
     */
    public function saveJournalFieldNamesAndVisibility(string $userId, array $fields): void
    {
        if (!$userId) {
            throw new RuntimeException("No user ID provided");
        }

        if (empty($fields)) {
            throw new RuntimeException("No fields provided");
        }

        $this->pdo->beginTransaction();

        try {
            // Clear old fields
            $del = $this->pdo->prepare("DELETE FROM journal_fields WHERE user_id = :uid");
            $del->execute([':uid' => $userId]);

            // Insert new values
            $insert = $this->pdo->prepare("
                INSERT INTO journal_fields (field_name, user_id, display_name, ordering, is_visible, width)
                VALUES (:field, :uid, :name, :ord, :vis)
            ");

            foreach ($fields as $f) {
                $insert->execute([
                    ':field' => $f->fieldName,
                    ':uid'   => $f->userId,
                    ':name'  => $f->displayName,
                    ':ord'   => $f->ordering,
                    ':vis'   => $f->isVisible ? 1 : 0,
                    ':width' => $f->width
                ]);
            }

            $this->pdo->commit();

        } catch (PDOException $e) {
            $this->pdo->rollBack();
            throw new RuntimeException("Failed to save journal fields: " . $e->getMessage());
        }
    }

    /**
     * NEW: Save column ordering using FIELD NAMES only.
     */
    public function updateColumnOrdering(array $orderedFieldNames): void
    {
        $this->pdo->beginTransaction();

        try {
            $update = $this->pdo->prepare("
                UPDATE journal_fields
                SET ordering = :o
                WHERE field_name = :f
            ");

            foreach ($orderedFieldNames as $index => $field) {
                $update->execute([
                    ':o' => $index + 1,
                    ':f' => $field
                ]);
            }

            $this->pdo->commit();

        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw new RuntimeException(
                "Failed to update column ordering: " . $e->getMessage()
            );
        }
    }

    public function updateColumnWidth(string $fieldName, int $width): void {
        $stmt = $this->pdo->prepare("
            UPDATE journal_fields
            SET width = :width
            WHERE field_name = :field_name
        ");

        $stmt->execute([
            ':width' => $width,
            ':field_name' => $fieldName
        ]);
    }

    public function updateJournalCell(int $id, string $field, string $value): void {
        // Safety: ensure field exists
        $validFields = $this->visibleJournalFieldNames();
        if (!in_array($field, $validFields, true)) {
            throw new RuntimeException("Invalid field: $field");
        }

        $stmt = $this->pdo->prepare("
            UPDATE trading_journal
            SET $field = :value
            WHERE id = :id
        ");

        $stmt->execute([
            ':value' => $value,
            ':id'    => $id
        ]);
    }

    public function getCheckOptions(string $fieldName): ?array {
        // Pull CREATE TABLE statement
        $stmt = $this->pdo->prepare("
            SELECT sql 
            FROM sqlite_master 
            WHERE type='table' AND name='trading_journal'
        ");
        $stmt->execute();

        $createSql = $stmt->fetchColumn();
        if (!$createSql) return null;

        // Regex:
        // CHECK(fieldName IN ('A','B','C'))
        $pattern = "/CHECK\s*\(\s*{$fieldName}\s+IN\s*\(([^)]*)\)\s*\)/i";

        if (!preg_match($pattern, $createSql, $matches)) {
            return null; // This field has no CHECK constraint list
        }

        // List inside parentheses: `'A','B','C'`
        $list = $matches[1];

        // Split into array and trim quotes/spaces
        return array_map(
            fn($v) => trim($v, " '\"\t\n\r"),
            explode(",", $list)
        );
    }
}
