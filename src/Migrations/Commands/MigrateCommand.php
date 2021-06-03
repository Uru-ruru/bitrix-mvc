<?php

namespace Uru\BitrixMigrations\Commands;

use Exception;
use Uru\BitrixMigrations\Migrator;

/**
 * Class MigrateCommand
 * @package Uru\BitrixMigrations\Commands
 */
class MigrateCommand extends AbstractCommand
{
    /**
     * Migrator instance.
     *
     * @var Migrator
     */
    protected Migrator $migrator;

    /**
     * @var string
     */
    protected static $defaultName = 'migrate';

    /**
     * Constructor.
     *
     * @param Migrator $migrator
     * @param string|null $name
     */
    public function __construct(Migrator $migrator, ?string $name = null)
    {
        $this->migrator = $migrator;

        parent::__construct($name);
    }

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setDescription('Run all outstanding migrations');
    }

    /**
     * Execute the console command.
     *
     * @return void
     * @throws Exception
     */
    protected function fire(): void
    {
        $toRun = $this->migrator->getMigrationsToRun();

        if (!empty($toRun)) {
            foreach ($toRun as $migration) {
                $this->migrator->runMigration($migration);
                $this->message("<info>Migrated:</info> $migration.php");
            }
        } else {
            $this->info('Nothing to migrate');
        }
    }
}
