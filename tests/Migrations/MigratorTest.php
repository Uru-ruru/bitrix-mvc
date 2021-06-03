<?php

namespace Uru\Tests\BitrixMigrations;

use Uru\BitrixMigrations\Migrator;
use Uru\BitrixMigrations\TemplatesCollection;
use Mockery as m;

class MigratorTest extends CommandTestCase
{
    /**
     * Tear down.
     */
    public function tearDown(): void
    {
        m::close();
    }

    public function testItCreatesMigration()
    {
        $database = $this->mockDatabase();
        $files = $this->mockFiles();

        $files->shouldReceive('createDirIfItDoesNotExist')->once();
        $files->shouldReceive('getContent')->once()->andReturn('some content');
        $files->shouldReceive('putContent')->once()->andReturn(1000);

        $migrator = $this->createMigrator($database, $files);

        $this->assertMatchesRegularExpression('/[0-9]{4}_[0-9]{2}_[0-9]{2}_[0-9]{6}_[0-9]{6}_test_migration/', $migrator->createMigration('test_migration', null));
    }

    /**
     * Create migrator.
     */
    protected function createMigrator($database, $files)
    {
        $config = [
            'table' => 'migrations',
            'dir' => 'migrations',
        ];

        $templatesCollection = new TemplatesCollection($config);
        $templatesCollection->registerBasicTemplates();

        return new Migrator($config, $templatesCollection, $database, $files);
    }

    /**
     * @return m\MockInterface
     */
    protected function mockDatabase()
    {
        return m::mock('Uru\BitrixMigrations\Interfaces\DatabaseStorageInterface');
    }

    /**
     * @return m\MockInterface
     */
    protected function mockFiles()
    {
        return m::mock('Uru\BitrixMigrations\Interfaces\FileStorageInterface');
    }
}
