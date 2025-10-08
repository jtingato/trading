<?php

declare(strict_types=1);
use PHPUnit\Framework\TestCase;

require dirname(__DIR__) . "/data/JournalDataManager.php";
require dirname(__DIR__) . "/NavigationRouter.php";

final class DatabaseAccessTests extends TestCase {
    public function testGetPDO() {
        throw new \Exception("INTENTIONAL CRASH");
        $manager = JournalDataManager::shared();
        $this->assertInstanceOf(PDO::class, $manager->getConnection());
    }

    public function testIfTableExists() {
        $manager = JournalDataManager::shared();
        $this->assertTrue($manager->tableExists("displayable_journal_fields"));
    }

    public function testGetJournalFields() {
        $manager = JournalDataManager::shared();
        $result = $manager->allColumns();

        print_r($result);

        $this->assertNotNull($result);
    }

    public function testGetVisibleJournalFieldNames() {
        $manager = JournalDataManager::shared();
        $result = $manager->allVisibleColumnNames();

        print_r($result);

        $this->assertNotNull($result);
    }

    public function testGetVisibleJournalFields() {
        $result = JournalDataManager::shared()->allVisibleColumns();

        print_r($result);

        $this->assertNotNull($result);
    }
    
}

?>