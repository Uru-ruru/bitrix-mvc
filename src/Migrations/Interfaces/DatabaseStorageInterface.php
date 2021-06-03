<?php

namespace Uru\BitrixMigrations\Interfaces;

/**
 * Interface DatabaseStorageInterface
 * @package Uru\BitrixMigrations\Interfaces
 */
interface DatabaseStorageInterface
{
    /**
     * Check if a given table already exists.
     *
     * @return bool
     */
    public function checkMigrationTableExistence(): bool;

    /**
     * Create migration table.
     *
     * @return void
     */
    public function createMigrationTable();

    /**
     * Get an array of migrations that have been ran previously.
     * Must be ordered by order asc.
     *
     * @return array
     */
    public function getRanMigrations(): array;

    /**
     * Save a migration name to the database to prevent it from running again.
     *
     * @param string $name
     *
     * @return void
     */
    public function logSuccessfulMigration(string $name): void;

    /**
     * Remove a migration name from the database so it can be run again.
     *
     * @param string $name
     *
     * @return void
     */
    public function removeSuccessfulMigrationFromLog(string $name): void;
}
