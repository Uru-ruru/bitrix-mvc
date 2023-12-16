<?php

namespace Uru\BitrixMigrations\Interfaces;

/**
 * Interface FileStorageInterface.
 */
interface FileStorageInterface
{
    /**
     * Get all of the migration files in a given path.
     */
    public function getMigrationFiles(string $path): array;

    /**
     * Require a file.
     *
     * @param mixed $path
     */
    public function requireFile($path): void;

    /**
     * Create a directory if it does not exist.
     *
     * @param mixed $dir
     */
    public function createDirIfItDoesNotExist($dir): void;

    /**
     * Get the content of a file.
     */
    public function getContent(string $path): string;

    /**
     * Write the contents of a file.
     */
    public function putContent(string $path, string $contents, bool $lock = false): int;

    /**
     * Check if file exists.
     */
    public function exists(string $path): bool;

    /**
     * Delete file.
     */
    public function delete(string $path): bool;
}
