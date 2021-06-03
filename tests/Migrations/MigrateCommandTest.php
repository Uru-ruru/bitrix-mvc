<?php

namespace Uru\Tests\BitrixMigrations;

use Mockery as m;

class MigrateCommandTest extends CommandTestCase
{
    protected function mockCommand($migrator)
    {
        return m::mock('Uru\BitrixMigrations\Commands\MigrateCommand[abort, info, message, getMigrationObjectByFileName]', [$migrator])
            ->shouldAllowMockingProtectedMethods();
    }

    public function testItMigratesNothingIfThereIsNoOutstandingMigrations()
    {
        $migrator = m::mock('Uru\BitrixMigrations\Migrator');
        $migrator->shouldReceive('getMigrationsToRun')->once()->andReturn([]);
        $migrator->shouldReceive('runMigration')->never();

        $command = $this->mockCommand($migrator);
        $command->shouldReceive('info')->with('Nothing to migrate')->once();

        $this->runCommand($command);
    }

    public function testItMigratesOutstandingMigrations()
    {
        $migrator = m::mock('Uru\BitrixMigrations\Migrator');
        $migrator->shouldReceive('getMigrationsToRun')->once()->andReturn([
            '2015_11_26_162220_bar',
        ]);
        $migrator->shouldReceive('runMigration')->with('2015_11_26_162220_bar')->once();

        $command = $this->mockCommand($migrator);
        $command->shouldReceive('message')->with('<info>Migrated:</info> 2015_11_26_162220_bar.php')->once();
        $command->shouldReceive('info')->with('Nothing to migrate')->never();

        $this->runCommand($command);
    }
}
