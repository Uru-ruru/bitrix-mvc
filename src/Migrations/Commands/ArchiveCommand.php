<?php

namespace Uru\BitrixMigrations\Commands;

use Symfony\Component\Console\Input\InputOption;
use Uru\BitrixMigrations\Migrator;

/**
 * Class ArchiveCommand.
 */
class ArchiveCommand extends AbstractCommand
{
    /**
     * Migrator instance.
     */
    protected Migrator $migrator;

    /**
     * @var string
     */
    protected static $defaultName = 'archive';

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
        $this->setDescription('Move migration into archive')
            ->addOption('without', 'w', InputOption::VALUE_REQUIRED, 'Archive without last N migration')
        ;
    }

    /**
     * Execute the console command.
     */
    protected function fire(): void
    {
        $files = $this->migrator->getAllMigrations();
        $without = $this->input->getOption('without') ?: 0;
        if ($without > 0) {
            $files = array_slice($files, 0, $without * -1);
        }

        $count = $this->migrator->moveMigrationFiles($files);

        if ($count) {
            $this->message("<info>Moved to archive:</info> {$count}");
        } else {
            $this->info('Nothing to move');
        }
    }
}
