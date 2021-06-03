<?php

namespace Uru\BitrixMigrations\Interfaces;

/**
 * Interface FileStorageInterface
 * @package Uru\BitrixMigrations\Interfaces
 */
interface FileStorageInterface
{
    /**
     * Get all of the migration files in a given path.
     *
     * @param string $path
     *
     * @return array
     */
    public function getMigrationFiles(string $path): array;

    /**
     * Require a file.
     *
     * @param $path
     *
     * @return void
     */
    public function requireFile($path): void;

    /**
     * Create a directory if it does not exist.
     *
     * @param $dir
     *
     * @return void
     */
    public function createDirIfItDoesNotExist($dir): void;

    /**
     * Get the content of a file.
     *
     * @param string $path
     *
     * @return string
     */
    public function getContent(string $path): string;

    /**
     * Write the contents of a file.
     *
     * @param string $path
     * @param string $contents
     * @param bool $lock
     *
     * @return int
     */
    public function putContent(string $path, string $contents, bool $lock = false): int;

    /**
     * Check if file exists.
     *
     * @param string $path
     *
     * @return bool
     */
    public function exists(string $path): bool;

    /**
     * Delete file.
     *
     * @param string $path
     *
     * @return bool
     */
    public function delete(string $path): bool;
}
