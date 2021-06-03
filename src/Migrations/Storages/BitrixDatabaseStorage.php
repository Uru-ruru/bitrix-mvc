<?php

namespace Uru\BitrixMigrations\Storages;

use Uru\BitrixMigrations\Interfaces\DatabaseStorageInterface;
use CDatabase;

/**
 * Class BitrixDatabaseStorage
 * @package Uru\BitrixMigrations\Storages
 */
class BitrixDatabaseStorage implements DatabaseStorageInterface
{
    /**
     * Bitrix $DB object.
     *
     * @var CDatabase
     */
    protected $db;

    /**
     * Table in DB to store migrations that have been already ran.
     *
     * @var string
     */
    protected string $table;

    /**
     * BitrixDatabaseStorage constructor.
     *
     * @param $table
     */
    public function __construct($table)
    {
        global $DB;

        $this->db = $DB;
        $this->table = $table;
    }

    /**
     * Check if a given table already exists.
     *
     * @return bool
     */
    public function checkMigrationTableExistence(): bool
    {
        return (bool)$this->db->query('SHOW TABLES LIKE "' . $this->table . '"')->fetch();
    }

    /**
     * Create migration table.
     *
     * @return void
     */
    public function createMigrationTable(): void
    {
        $this->db->query("CREATE TABLE {$this->table} (ID INT NOT NULL AUTO_INCREMENT, MIGRATION VARCHAR(255) NOT NULL, PRIMARY KEY (ID))");
    }

    /**
     * Get an array of migrations the have been ran previously.
     * Must be ordered by order asc.
     *
     * @return array
     */
    public function getRanMigrations(): array
    {
        $migrations = [];

        $dbRes = $this->db->query("SELECT MIGRATION FROM {$this->table} ORDER BY ID ASC");
        while ($result = $dbRes->fetch()) {
            $migrations[] = $result['MIGRATION'];
        }

        return $migrations;
    }

    /**
     * Save migration name to the database to prevent it from running again.
     *
     * @param string $name
     *
     * @return void
     */
    public function logSuccessfulMigration(string $name): void
    {
        $this->db->insert($this->table, [
            'MIGRATION' => "'" . $this->db->forSql($name) . "'",
        ]);
    }

    /**
     * Remove a migration name from the database so it can be run again.
     *
     * @param string $name
     *
     * @return void
     */
    public function removeSuccessfulMigrationFromLog(string $name): void
    {
        $this->db->query("DELETE FROM {$this->table} WHERE MIGRATION = '" . $this->db->forSql($name) . "'");
    }

    /**
     * Start transaction
     */
    public function startTransaction()
    {
        $this->db->StartTransaction();
    }

    /**
     * Commit transaction
     */
    public function commitTransaction()
    {
        $this->db->Commit();
    }

    /**
     * Rollback transaction
     */
    public function rollbackTransaction()
    {
        $this->db->Rollback();
    }
}
