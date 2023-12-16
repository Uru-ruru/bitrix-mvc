<?php

namespace Uru\BitrixMigrations\Interfaces;

/**
 * Interface MigrationInterface.
 */
interface MigrationInterface
{
    /**
     * Run the migration.
     */
    public function up(): void;

    /**
     * Reverse the migration.
     */
    public function down(): void;

    /**
     * use transaction.
     */
    public function useTransaction(bool $default = false): bool;
}
