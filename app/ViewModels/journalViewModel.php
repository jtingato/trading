<?php
declare(strict_types=1);

namespace Monarch\ViewModels;

use Monarch\Data\JournalDataManager;
use Monarch\Data\Models\JournalField;
use Throwable;

class JournalViewModel
{
    /** @var JournalField[] */
    public array $journalFields = [];
    /** */
    public array $dropdownFields = [];

    /** @var string[] field names in the correct order */
    public array $journalHeaderNames = [];

    /** @var array<int, array<string, mixed>> */
    public array $rows = [];

    public string $errorMsg = '';

    private JournalDataManager $dataManager;

    // TODO: Replace with actual user ID later
    private string $currentUser = 'user_123';

    public function __construct()
    {
        $this->dataManager        = JournalDataManager::shared();
        $this->journalFields      = $this->dataManager->getJournalFields();

        // IMPORTANT: use FIELD NAMES for ordering
        $this->journalHeaderNames = $this->dataManager->visibleJournalFieldNames();

        $this->getJournalEntries();
    }

    public function getJournalEntries(): void
    {
        $this->rows = $this->dataManager->getJournalEntries($this->journalHeaderNames);

        foreach ($this->journalHeaderNames as $fieldName) {
            $this->dropdownFields[$fieldName] = $this->dataManager->getDropdownOptions($fieldName);
        }
    }

    /**
     * Called when user saves field visibility + display names.
     */
    public function updateJournalFieldNamesAndVisibility(): void
    {
        if (!isset($_POST['fields']) || !is_array($_POST['fields'])) {
            $this->errorMsg = "No fields were submitted.";
            return;
        }

        $submitted = $_POST['fields'];

        try {
            foreach ($submitted as $fieldId => $props) {

                $field = $this->findJournalField($fieldId);
                if (!$field) continue;

                $field->fieldName   = $fieldId;
                $field->userId      = $this->currentUser;
                $field->displayName = $props['displayName'] ?? $fieldId;
                $field->isVisible   = isset($props['isVisible']) && (int)$props['isVisible'] === 1;

                // Ordering: only meaningful when visible
                if (!$field->isVisible) {
                    $field->ordering = 0;
                }
            }

        } catch (Throwable $e) {
            $this->errorMsg = "Failed to update fields: " . $e->getMessage();
            return;
        }

        try {
            $this->dataManager->saveJournalFieldNamesAndVisibility(
                $this->currentUser,
                $this->journalFields
            );
        } catch (Throwable $e) {
            $this->errorMsg = "Failed to save fields: " . $e->getMessage();
        }
    }

    public function findJournalField(string $field): ?JournalField
    {
        foreach ($this->journalFields as $jf) {
            if ($jf->fieldName === $field) {
                return $jf;
            }
        }
        return null;
    }

    /**
     * FIELD NAME → DISPLAY NAME
     */
    public function displayNameFromFieldName(string $field): string
    {
        $jf = $this->findJournalField($field);
        return $jf ? ($jf->displayName ?? $jf->fieldName) : $field;
    }

    /**
     * Array of FIELD NAMES → Array of DISPLAY NAMES
     */
    public function displayNamesFromFieldNames(array $fieldNames): array
    {
        return array_map(fn($f) => $this->displayNameFromFieldName($f), $fieldNames);
    }

}
