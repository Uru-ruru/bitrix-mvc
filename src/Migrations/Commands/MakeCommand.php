<?php

namespace Uru\BitrixMigrations\Commands;

use Exception;
use Uru\BitrixMigrations\Migrator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class MakeCommand
 * @package Uru\BitrixMigrations\Commands
 */
class MakeCommand extends AbstractCommand
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
    protected static $defaultName = 'make';

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
        $this->setDescription('Create a new migration file')
            ->addArgument(
                'name',
                InputArgument::REQUIRED,
                'The name of the migration'
            )
            ->addOption(
                'template',
                't',
                InputOption::VALUE_REQUIRED,
                'Migration template'
            )
            ->addOption(
                'directory',
                'd',
                InputOption::VALUE_REQUIRED,
                'Migration directory'
            );
    }

    /**
     * Execute the console command.
     *
     * @return void
     * @throws Exception
     */
    protected function fire(): void
    {
        $migration = $this->migrator->createMigration(
            $this->input->getArgument('name'),
            $this->input->getOption('template'),
            [],
            $this->input->getOption('directory')
        );

        $this->message("<info>Migration created:</info> $migration.php");
    }
}
