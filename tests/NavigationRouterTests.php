<?php

// Link to video tutorial
// https://www.youtube.com/watch?v=AJuSQaDQXfA

declare(strict_types=1);
use PHPUnit\Framework\TestCase;

require dirname(__DIR__) . "/NavigationRouter.php";
final class NavigationRouterTests extends TestCase {

	public function testFindBaseUsingDefaultRootFolderName() {
		$router = new NavigationRouter();
		$expectedResult = dirname(__DIR__);
		echo "\n🛑 Expected path: {$expectedResult} \n";

		$testFolders = [dirname(__DIR__) . "/pages/journal", dirname(__DIR__) . "/css"] ;

		foreach ($testFolders as $folder) {
			$result = $router->findMonarchBase($folder);
			echo "\n🛑 Test path: {$folder} \n";
			$this->assertNotNull($result, "The search or the base directory was null");
			echo "\n🛑 " . $result . "\n";
			$this->assertSame($expectedResult, $result);
		}
	}

	public function testFindBaseProvidingRootFolderName() {
		$router = new NavigationRouter();
		$expectedResult = dirname(__DIR__);
		echo "\n🛑 Expected path: " . $expectedResult . "\n";

		$testFolders = [dirname(__DIR__) . "/pages/journal", dirname(__DIR__) . "/css"] ;

		foreach ($testFolders as $folder) {
			$result = $router->findMonarchBase($folder, 'monarch');
			echo "\n🛑 Test path: " . $folder . "\n";
			$this->assertNotNull($result, "The search or the base directory was null");
			echo "\n🛑 " . $result . "\n";
			$this->assertSame($expectedResult, $result);
		}
	}

	public function testFindBaseUsingIncorrectDefaultRootFolderName() {
		$router = new NavigationRouter();

		// NOTE: this assumes that this test file is only one level up from the root directory
		$expectedResult = dirname(__DIR__);
		echo "\n🛑 Expected path: " . $expectedResult . "\n";

		$testFolders = [dirname(__DIR__) . "/pages/journal", dirname(__DIR__) . "/css"] ;

		foreach ($testFolders as $folder) {
			$result = $router->findMonarchBase($folder, "archie");
			echo "\n🛑 Test path: " . $folder . "\n";
			$this->assertNull($result, "The search or the base directory was null");
		}
	}

	public function testPathForFileNamed() {
		// Capitalization matters here.  Test values MUST match exactly to the file name and path folders.
		$router = new NavigationRouter();
		$testFiles = [
			"index.php" => "/Users/johningato/Sites/monarch/index.php",	
			"journal.css" => "/Users/johningato/Sites/monarch/pages/journal/journal.css",	
			"sql.php" => "/Users/johningato/Sites/monarch/data/sql.php",
			"JournalField.php" =>  "/Users/johningato/Sites/monarch/data/Models/JournalField.php"
		];

		forEach($testFiles as $key => $value) {
			$this->assertSame($value, $router->pathForFileNamed($key));
		}
	}
}
