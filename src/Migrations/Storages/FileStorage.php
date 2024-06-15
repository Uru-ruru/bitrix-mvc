<?php

namespace Uru\BitrixMigrations\Storages;

use Uru\BitrixMigrations\Helpers;
use Uru\BitrixMigrations\Interfaces\FileStorageInterface;

/**
 * Class FileStorage.
 */
class FileStorage implements FileStorageInterface
{
    /**
     * Get all of the migration files in a given path.
     */
    public function getMigrationFiles(string $path): array
    {
        $files = Helpers::rGlob($path.'/*_*.php');

        if (!$files) {
            return [];
        }

        $files = array_map(function ($file) {
            return str_replace('.php', '', basename($file));
        }, $files);

        sort($files);

        return $files;
    }

    /**
     * Require a file.
     *
     * @param mixed $path
     */
    public function requireFile($path): void
    {
        require_once $path;
    }

    /**
     * Create a directory if it does not exist.
     *
     * @param mixed $dir
     */
    public function createDirIfItDoesNotExist($dir): void
    {
        if (!file_exists($dir)) {
            mkdir($dir, 0o755, true);
        }
    }

    /**
     * Get the content of a file.
     *
     * @throws \Exception
     */
    public function getContent(string $path): string
    {
        if (!file_exists($path)) {
            throw new \Exception("File does not exist at path {$path}");
        }

        return file_get_contents($path);
    }

    /**
     * Write the contents of a file.
     */
    public function putContent(string $path, string $contents, bool $lock = false): int
    {
        return file_put_contents($path, $contents, $lock ? LOCK_EX : 0);
    }

    /**
     * Check if file exists.
     */
    public function exists(string $path): bool
    {
        return file_exists($path);
    }

    /**
     * Delete file.
     */
    public function delete(string $path): bool
    {
        return $this->exists($path) && unlink($path);
    }

    /**
     * Move file.
     */
    public function move(string $path_from, string $path_to): bool
    {
        return $this->exists($path_from) && rename($path_from, $path_to);
    }
}
