<?php
declare(strict_types=1);

namespace Monarch\Data\Models;

class JournalField
{
    public string $fieldName;
    public string $userId;
    public ?int $ordering;
    public ?string $displayName;
    public bool $isVisible;

    public function __construct(
        string $name = '',
        string $userId = '',
        ?int $ordering = null,
        ?string $displayName = null,
        bool $isVisible = true
    ) {
        $this->fieldName   = $name;
        $this->userId      = $userId;
        $this->ordering    = $ordering;
        $this->displayName = $displayName;
        $this->isVisible   = $isVisible;
    }

    /**
     * Factory helper that builds a JournalField from a database row.
     *
     * Example $data keys:
     * - field_name
     * - user_id
     * - ordering
     * - display_name
     * - is_visible
     */
    public static function fromArray(array $data): JournalField
    {
        return new JournalField(
            $data['field_name'] ?? '',
            $data['user_id'] ?? '',
            isset($data['ordering']) ? (int)$data['ordering'] : null,
            $data['display_name'] ?? null,
            !empty($data['is_visible'])
        );
    }

    public function toArray(): array
    {
        return [
            'field_name'   => $this->fieldName,
            'user_id'      => $this->userId,
            'ordering'     => $this->ordering,
            'display_name' => $this->displayName,
            'is_visible'   => $this->isVisible ? 1 : 0
        ];
    }
}
