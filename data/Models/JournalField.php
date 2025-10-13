<?php
    class JournalField {
        public string $fieldName;
        public string $userId;
        public ?int $ordering;
        public ?string $displayName;
        public bool $isVisible;

        public function __construct(
            string $name = "",
            string $user_id = "",
            ?int $ordering = null,
            ?string $display_name = null,
            bool $isVisible = true
        ) {
            $this->fieldName = $name;
            $this->userId = $user_id;
            $this->ordering = $ordering;
            $this->displayName = $display_name;
            $this->isVisible = $isVisible;
        }

        public static function journalFieldWith(array $data) {
            $newJF = new JournalField("", "", "","","");
            $newJF->fieldName = $data['name'];
            $newJF->userId = $data['user_id'];
            $newJF->ordering = $data['ordering'];
            $newJF->displayName = $data['display_name'];
            $newJF->isVisible = $data['isVisible'];
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

        public function displayName(): ?string {
            return $this->displayName;
        }

        public function isVisible(): bool {
            return $this->isVisible;
        }

        public function toArray(): array {
            return [
                'field_name' => $this->fieldName,
                'user_id' => $this->userId,
                'ordering' => $this->ordering,
                'display_name' => $this->displayName,
                'is_visible' => $this->isVisible ? 1 : 0
            ];
        }

        // public function createStatement() {
        //     $query = "SELECT sql FROM sqlite_master WHERE type='table' AND name='trading_journal'";
        //     $createStmt = JournalDataManager::shared().executeS

        // }
    }
?>