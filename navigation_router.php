<!-- Navigation Helper -->
<?php

class NavigationRouter {
	public readonly string $rootDir;
	public function __constuct(string $rootDir) {
		$this->rootDir = $rootDir;
	}

	function pathForFileNamed($name){
		$siteBaseDir = $this->findMonarchBase();
		return $this->findFileRecursive($siteBaseDir, $name);
	}

	function findMonarchBase($startDir = __DIR__, $rootFolderName = 'monarch'): ?string {
		$current = realpath($startDir);
	
		while ($current !== false) {
			if (basename($current) === $rootFolderName) {
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

	function findFileRecursive (string $rootDir, string $targetFile): ?string {
    	$iterator = new RecursiveIteratorIterator (
        	new RecursiveDirectoryIterator( $rootDir, FilesystemIterator::SKIP_DOTS),
        	RecursiveIteratorIterator::SELF_FIRST
    	);

		foreach ($iterator as $file) {
			if ($file->isFile() && $file->getFilename() === $targetFile) {
				return $file->getPathname(); // Full path to the file
			}
		}

    	return null; // File not found
	}
}


?>