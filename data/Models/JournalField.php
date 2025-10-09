<?php
    class JournalField {
        public string $fieldName;
        public string $userId;
        public ?int $ordering;
        public ?string $friendlyName;
        public bool $isVisible;

        public function __construct(
            string $name,
            string $user_id,
            ?int $ordering = null,
            ?string $friendly_name = null,
            bool $isVisible = true
        ) {
            $this->fieldName = $name;
            $this->userId = $user_id;
            $this->ordering = $ordering;
            $this->friendlyName = $friendly_name;
            $this->isVisible = $isVisible;
        }

        // Optional: Add getters if you want encapsulation
        public function fieldName(): string {
            return $this->fieldName;
        }

        public function userId(): string {
            return $this->userId;
        }

        public function ordering(): ?int {
            return $this->ordering;
        }

        public function friendlyName(): ?string {
            return $this->friendlyName;
        }

        public function isVisible(): bool {
            return $this->isVisible;
        }

        public function toArray(): array {
            return [
                'field_name' => $this->fieldName,
                'user_id' => $this->userId,
                'ordering' => $this->ordering,
                'friendly_name' => $this->friendlyName,
                'is_visible' => $this->isVisible ? 1 : 0
            ];
        }
    }
?>