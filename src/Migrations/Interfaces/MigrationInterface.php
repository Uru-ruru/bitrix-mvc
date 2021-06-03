<?php

namespace Uru\BitrixMigrations\Interfaces;

/**
 * Interface MigrationInterface
 * @package Uru\BitrixMigrations\Interfaces
 */
interface MigrationInterface
{
    /**
     * Run the migration.
     *
     * @return void
     */
    public function up(): void;

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down(): void;

    /**
     * use transaction
     *
     * @param bool $default
     * @return bool
     */
    public function useTransaction(bool $default = false): bool;
}
