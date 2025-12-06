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


    /** Return array of visible fields ordered */
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


    /** Return all actual DB column names */
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


    /** Fetch all JournalField models */
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


    /** Fetch trading_journal rows in the chosen order */
    public function getJournalEntries(array $columnList = []): array
    {
        if (!in_array('id', $columnList, true)) {
            $columnList = array_merge(['id'], $columnList);
        }

        $columns = implode(', ', $columnList);
        $stmt = $this->pdo->query("SELECT {$columns} FROM trading_journal");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

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


    /** Save field list + visibility */
    public function saveJournalFieldNamesAndVisibility(string $userId, array $fields): void
    {
        if (!$userId) throw new RuntimeException("No user ID provided");
        if (empty($fields)) throw new RuntimeException("No fields provided");

        $this->pdo->beginTransaction();

        try {
            $del = $this->pdo->prepare("DELETE FROM journal_fields WHERE user_id = :uid");
            $del->execute([':uid' => $userId]);

            $insert = $this->pdo->prepare("
                INSERT INTO journal_fields (field_name, user_id, display_name, ordering, is_visible, width)
                VALUES (:field, :uid, :name, :ord, :vis, :width)
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

        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw new RuntimeException("Failed to save journal fields: " . $e->getMessage());
        }
    }


    /** Save column ordering */
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


    /** Update a journal cell value */
    public function updateJournalCell(int $id, string $field, string $value): void {
        $validFields = $this->allJournalFieldNames(); // <-- FIXED

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



    /* ------------------------------ */
    /* Dropdown Options Storage (NEW) */
    /* ------------------------------ */

    public function getDropdownOptions(string $fieldName): array
    {
        $stmt = $this->pdo->prepare("
            SELECT option_value 
            FROM journal_dropdown_options 
            WHERE field_name = :f 
            ORDER BY option_value ASC
        ");
        $stmt->execute([':f' => $fieldName]);

        return $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
    }

    public function addDropdownOption(string $field, string $value): bool
    {
        try {
            $stmt = $this->pdo->prepare("
                INSERT OR IGNORE INTO journal_dropdown_options (field_name, option_value)
                VALUES (:field, :value)
            ");
            return $stmt->execute([
                ':field' => $field,
                ':value' => $value
            ]);
        } catch (Throwable $e) {
            return false;
        }
    }
}
