<!-- Navigation Helper -->
<?php

class NavigationRouter {
	function includePage($page) {
		$path = $this->pathForItem($page);
		if (file_exists($path)) {
			include $path;
		} else {
			include BASE_PATH . '/pages/404.php';
		}
	}

	function getFilePath($file) {
		$path = $this->pathForItem($file);
		if (file_exists($path)) {
			return $path;
		} else {
			echo "<h2>404 Page not found</h2>";
		}
	}

	private function pathForItem($pageName) {
        $routes = [
            'home'    => '/pages/home.php',
            'journal' => '/pages/journal/journal.php',
            'sql'     => '/data/sql.php',
            'database'=> '/data/monarch.db', // maybe not include directly?
        ];

        return isset($routes[$pageName]) ? BASE_PATH . $routes[$pageName] : null;
    }

	function findMonarchBase($startDir = __DIR__): ?string {
		$current = realpath($startDir);
	
		while ($current !== false) {
			if (basename($current) === 'monarch') {
				return $current;
			}
	
			$parent = dirname($current);
			if ($parent === $current) {
				// Reached filesystem root
				break;
			}
	
			$current = $parent;
		}
	
		return null; // "Monarch" not found
	}
}

?>