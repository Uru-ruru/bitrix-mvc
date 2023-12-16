<?php

namespace Uru\BitrixMigrations\Commands;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Uru\BitrixMigrations\Migrator;

class RollbackCommand extends AbstractCommand
{
    /**
     * Migrator instance.
     */
    protected Migrator $migrator;

    protected static $defaultName = 'rollback';

    /**
     * Constructor.
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
        $this->setDescription('Rollback the last migration')
            ->addOption('hard', null, InputOption::VALUE_NONE, 'Rollback without running down()')
            ->addOption('delete', null, InputOption::VALUE_NONE, 'Delete migration file after rolling back')
        ;
    }

    /**
     * Execute the console command.
     *
     * @throws \Exception
     */
    protected function fire(): void
    {
        $ran = $this->migrator->getRanMigrations();

        if (empty($ran)) {
            $this->info('Nothing to rollback');

            return;
        }

        $migration = $ran[count($ran) - 1];

        $this->input->getOption('hard')
            ? $this->hardRollbackMigration($migration)
            : $this->rollbackMigration($migration);

        $this->deleteIfNeeded($migration);
    }

    /**
     * Call rollback.
     *
     * @param mixed $migration
     *
     * @throws \Exception
     */
    protected function rollbackMigration($migration): void
    {
        if ($this->migrator->doesMigrationFileExist($migration)) {
            $this->migrator->rollbackMigration($migration);
        } else {
            $this->markRolledBackWithConfirmation($migration);
        }

        $this->message("<info>Rolled back:</info> {$migration}.php");
    }

    /**
     * Call hard rollback.
     *
     * @param mixed $migration
     */
    protected function hardRollbackMigration($migration): void
    {
        $this->migrator->removeSuccessfulMigrationFromLog($migration);

        $this->message("<info>Rolled back with --hard:</info> {$migration}.php");
    }

    /**
     * Ask a user to confirm rolling back non-existing migration and remove it from log.
     *
     * @param mixed $migration
     */
    protected function markRolledBackWithConfirmation($migration): void
    {
        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion("<error>Migration {$migration} was not found.\r\nDo you want to mark it as rolled back? (y/n)</error>\r\n", false);

        if (!$helper->ask($this->input, $this->output, $question)) {
            $this->abort();
        }

        $this->migrator->removeSuccessfulMigrationFromLog($migration);
    }

    /**
     * Delete migration file if options is set.
     *
     * @throws \Exception
     */
    protected function deleteIfNeeded(string $migration): void
    {
        if (!$this->input->getOption('delete')) {
            return;
        }

        if ($this->migrator->deleteMigrationFile($migration)) {
            $this->message("<info>Deleted:</info> {$migration}.php");
        }
    }
}
