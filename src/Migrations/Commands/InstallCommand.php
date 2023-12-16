<?php

namespace Uru\BitrixMigrations\Commands;

use Uru\BitrixMigrations\Interfaces\DatabaseStorageInterface;

/**
 * Class InstallCommand.
 */
class InstallCommand extends AbstractCommand
{
    /**
     * Interface that gives us access to the database.
     */
    protected DatabaseStorageInterface $database;

    /**
     * Table in DB to store migrations that have been already run.
     */
    protected string $table;

    /**
     * @var string
     */
    protected static $defaultName = 'install';

    /**
     * Constructor.
     *
     * @param string $table
     */
    public function __construct($table, DatabaseStorageInterface $database, ?string $name = null)
    {
        $this->table = $table;
        $this->database = $database;

        parent::__construct($name);
    }

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setDescription('Create the migration database table');
    }

    /**
     * Execute the console command.
     */
    protected function fire(): void
    {
        if ($this->database->checkMigrationTableExistence()) {
            $this->abort("Table \"{$this->table}\" already exists");
        }

        $this->database->createMigrationTable();

        $this->info('Migration table has been successfully created!');
    }
}
