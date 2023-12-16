<?php

namespace Uru\BitrixMigrations\Interfaces;

/**
 * Interface DatabaseStorageInterface.
 */
interface DatabaseStorageInterface
{
    /**
     * Check if a given table already exists.
     */
    public function checkMigrationTableExistence(): bool;

    /**
     * Create migration table.
     */
    public function createMigrationTable();

    /**
     * Get an array of migrations that have been ran previously.
     * Must be ordered by order asc.
     */
    public function getRanMigrations(): array;

    /**
     * Save a migration name to the database to prevent it from running again.
     */
    public function logSuccessfulMigration(string $name): void;

    /**
     * Remove a migration name from the database so it can be run again.
     */
    public function removeSuccessfulMigrationFromLog(string $name): void;
}
