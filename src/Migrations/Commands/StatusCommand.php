<?php

namespace Uru\BitrixMigrations\Commands;

use Uru\BitrixMigrations\Migrator;

class StatusCommand extends AbstractCommand
{
    /**
     * Migrator instance.
     *
     * @var Migrator
     */
    protected $migrator;

    protected static $defaultName = 'status';

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
        $this->setDescription('Show status about last migrations');
    }

    /**
     * Execute the console command.
     */
    protected function fire(): void
    {
        $this->showOldMigrations();

        $this->output->write("\r\n");

        $this->showNewMigrations();
    }

    /**
     * Show old migrations.
     */
    protected function showOldMigrations()
    {
        $old = collect($this->migrator->getRanMigrations());

        $this->output->writeln("<fg=yellow>Old migrations:\r\n</>");

        $max = 5;
        if ($old->count() > $max) {
            $this->output->writeln('<fg=yellow>...</>');

            $old = $old->take(-$max);
        }

        foreach ($old as $migration) {
            $this->output->writeln("<fg=yellow>{$migration}.php</>");
        }
    }

    /**
     * Show new migrations.
     */
    protected function showNewMigrations()
    {
        $new = collect($this->migrator->getMigrationsToRun());

        $this->output->writeln("<fg=green>New migrations:\r\n</>");

        foreach ($new as $migration) {
            $this->output->writeln("<fg=green>{$migration}.php</>");
        }
    }
}
