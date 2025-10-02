<?php

// Link to video tutorial
// https://www.youtube.com/watch?v=AJuSQaDQXfA

declare(strict_types=1);
use PHPUnit\Framework\TestCase;

require dirname(__DIR__) . "/navigation_router.php";
final class NavigationRouterTests extends TestCase {
    public function testTwoValuesAreTheSame() {
		$this->assertSame(1,1);
	} 

	public function testFindBase() {
		$router = new NavigationRouter();
		$expectedResult = dirname(__DIR__);
		echo "\n🛑🛑🛑 Test path: " . $expectedResult . "\n";

		$journalCSS = dirname(__DIR__) . "/pages/journal" ;

		$result = $router->findMonarchBase($journalCSS);
		echo "\n🛑🛑🛑 Test path: " . $journalCSS . "\n";
		$this->assertNotNull($result, "The search or the base directory was null");
		echo "\n🛑🛑🛑 " . $result . "\n";
		$this->assertSame($expectedResult, $result);

		
	}
}
