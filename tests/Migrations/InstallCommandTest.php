<?php

namespace Uru\Tests\BitrixMigrations;

use Mockery as m;

class InstallCommandTest extends CommandTestCase
{
    protected function mockCommand($database)
    {
        return m::mock('Uru\BitrixMigrations\Commands\InstallCommand[abort]', ['migrations', $database])
            ->shouldAllowMockingProtectedMethods();
    }

    public function testItCreatesMigrationTable()
    {
        $database = m::mock('Uru\BitrixMigrations\Interfaces\DatabaseStorageInterface');
        $database->shouldReceive('checkMigrationTableExistence')->once()->andReturn(false);
        $database->shouldReceive('createMigrationTable')->once();

        $command = $this->mockCommand($database);

        $this->runCommand($command);
    }

    public function testItDoesNotCreateATableIfItExists()
    {
        $database = m::mock('Uru\BitrixMigrations\Interfaces\DatabaseStorageInterface');
        $database->shouldReceive('checkMigrationTableExistence')->once()->andReturn(true);
        $database->shouldReceive('createMigrationTable')->never();

        $command = $this->mockCommand($database);
        $command->shouldReceive('abort')->once()->andThrow('DomainException');

        $this->runCommand($command);

    }
}
