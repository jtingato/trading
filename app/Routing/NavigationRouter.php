<?php
declare(strict_types=1);

namespace Monarch\Routing;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class NavigationRouter
{
    private string $basePath;

    public function __construct(?string $basePath = null)
    {
        // Get project root (folder containing index.php)
        $root = dirname(__DIR__, 1) . '/../';

        // Pages folder
        $this->basePath = $basePath ?? $root . 'pages/';
    }

    /**
     * Searches recursively inside /pages/ for a file with the given name.
     */
    public function pathForFileNamed(string $name): ?string
    {
        $name = basename($name); // security

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $this->basePath,
                FilesystemIterator::SKIP_DOTS
            ),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getFilename() === $name) {
                return $file->getPathname();
            }
        }

        return null;
    }
}
