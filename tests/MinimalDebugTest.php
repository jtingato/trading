<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class MinimalDebugTest extends TestCase {
    public function testBreak() {
        error_log("MINIMAL TEST STARTED");
        xdebug_break();
        sleep(10);
        $this->assertTrue(true);
    }
}