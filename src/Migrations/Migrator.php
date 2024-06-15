<?php

namespace Uru\BitrixMigrations;

use Uru\BitrixIblockHelper\HLBlock;
use Uru\BitrixIblockHelper\IblockId;
use Uru\BitrixMigrations\Constructors\FieldConstructor;
use Uru\BitrixMigrations\Interfaces\DatabaseStorageInterface;
use Uru\BitrixMigrations\Interfaces\FileStorageInterface;
use Uru\BitrixMigrations\Interfaces\MigrationInterface;
use Uru\BitrixMigrations\Storages\BitrixDatabaseStorage;
use Uru\BitrixMigrations\Storages\FileStorage;

/**
 * Class Migrator.
 *
 * @deprecated
 */
class Migrator
{
    /**
     * Migrator configuration array.
     */
    protected array $config;

    /**
     * Directory to store m.
     *
     * @var string
     */
    protected $dir;

    /**
     * Directory to store archive m.
     *
     * @var string
     */
    protected $dir_archive;

    /**
     * User transaction default.
     *
     * @var bool
     */
    protected $use_transaction;

    /**
     * Files interactions.
     *
     * @var FileStorageInterface
     */
    protected $files;

    /**
     * Interface that gives us access to the database.
     *
     * @var DatabaseStorageInterface
     */
    protected $database;

    /**
     * TemplatesCollection instance.
     */
    protected TemplatesCollection $templates;

    /**
     * Constructor.
     */
    public function __construct(array $config, TemplatesCollection $templates, ?DatabaseStorageInterface $database = null, ?FileStorageInterface $files = null)
    {
        $this->config = $config;
        $this->dir = $config['dir'];
        $this->dir_archive = $config['dir_archive'] ?? 'archive';
        $this->use_transaction = $config['use_transaction'] ?? false;

        if (isset($config['default_fields']) && is_array($config['default_fields'])) {
            foreach ($config['default_fields'] as $class => $default_fields) {
                FieldConstructor::$defaultFields[$class] = $default_fields;
            }
        }

        $this->templates = $templates;
        $this->database = $database ?: new BitrixDatabaseStorage($config['table']);
        $this->files = $files ?: new FileStorage();
    }

    /**
     * Create migration file.
     *
     * @param string $name    - migration name
     * @param array  $replace - array of placeholders that should be replaced with a given values
     *
     * @throws \Exception
     */
    public function createMigration(string $name, ?string $templateName, array $replace = [], ?string $subDir = ''): string
    {
        $targetDir = $this->dir;
        $subDir = trim(str_replace('\\', '/', $subDir), '/');
        if ($subDir) {
            $targetDir .= '/'.$subDir;
        }

        $this->files->createDirIfItDoesNotExist($targetDir);

        $fileName = $this->constructFileName($name);
        $className = $this->getMigrationClassNameByFileName($fileName);
        $templateName = $this->templates->selectTemplate($templateName);

        $template = $this->files->getContent($this->templates->getTemplatePath($templateName));
        $template = $this->replacePlaceholdersInTemplate($template, array_merge($replace, ['className' => $className]));

        $this->files->putContent($targetDir.'/'.$fileName.'.php', $template);

        return $fileName;
    }

    /**
     * Run all migrations that were not run before.
     *
     * @throws \Exception
     */
    public function runMigrations(): array
    {
        $migrations = $this->getMigrationsToRun();
        $ran = [];

        if (empty($migrations)) {
            return $ran;
        }

        foreach ($migrations as $migration) {
            $this->runMigration($migration);
            $ran[] = $migration;
        }

        return $ran;
    }

    /**
     * Run a given migration.
     *
     * @throws \Exception
     */
    public function runMigration(string $file): void
    {
        $migration = $this->getMigrationObjectByFileName($file);

        $this->disableBitrixIblockHelperCache();

        $this->checkTransactionAndRun($migration, function () use ($migration, $file) {
            if (false === $migration->up()) {
                throw new \Exception("Migration up from {$file}.php returned false");
            }
        });

        $this->logSuccessfulMigration($file);
    }

    /**
     * Log successful migration.
     */
    public function logSuccessfulMigration(string $migration): void
    {
        $this->database->logSuccessfulMigration($migration);
    }

    /**
     * Get ran migrations.
     */
    public function getRanMigrations(): array
    {
        return $this->database->getRanMigrations();
    }

    /**
     * Get all migrations.
     */
    public function getAllMigrations(): array
    {
        return $this->files->getMigrationFiles($this->dir);
    }

    /**
     * Determine whether migration file for migration exists.
     *
     * @throws \Exception
     */
    public function doesMigrationFileExist(string $migration): bool
    {
        return $this->files->exists($this->getMigrationFilePath($migration));
    }

    /**
     * Rollback a given migration.
     *
     * @throws \Exception
     */
    public function rollbackMigration(string $file)
    {
        $migration = $this->getMigrationObjectByFileName($file);

        $this->checkTransactionAndRun($migration, function () use ($migration, $file) {
            if (false === $migration->down()) {
                throw new \Exception("<error>Can't rollback migration:</error> {$file}.php");
            }
        });

        $this->removeSuccessfulMigrationFromLog($file);
    }

    /**
     * Remove a migration name from the database so it can be run again.
     */
    public function removeSuccessfulMigrationFromLog(string $file)
    {
        $this->database->removeSuccessfulMigrationFromLog($file);
    }

    /**
     * Delete migration file.
     *
     * @throws \Exception
     */
    public function deleteMigrationFile(string $migration): bool
    {
        return $this->files->delete($this->getMigrationFilePath($migration));
    }

    /**
     * Get array of migrations that should be ran.
     */
    public function getMigrationsToRun(): array
    {
        $allMigrations = $this->getAllMigrations();

        $ranMigrations = $this->getRanMigrations();

        return array_diff($allMigrations, $ranMigrations);
    }

    /**
     * Move migration files.
     *
     * @throws \Exception
     */
    public function moveMigrationFiles(array $files = [], string $toDir = ''): int
    {
        $toDir = trim($toDir ?: $this->dir_archive, '/');
        $files = $files ?: $this->getAllMigrations();
        $this->files->createDirIfItDoesNotExist("{$this->dir}/{$toDir}");

        $count = 0;
        foreach ($files as $migration) {
            $from = $this->getMigrationFilePath($migration);
            $to = "{$this->dir}/{$toDir}/{$migration}.php";

            if ($from == $to) {
                continue;
            }

            $flag = $this->files->move($from, $to);

            if ($flag) {
                ++$count;
            }
        }

        return $count;
    }

    /**
     * Construct migration file name from migration name and current time.
     */
    protected function constructFileName(string $name): string
    {
        [$usec, $sec] = explode(' ', microtime());

        $usec = substr($usec, 2, 6);

        return date('Y_m_d_His', $sec).'_'.$usec.'_'.$name;
    }

    /**
     * Get a migration class name by a migration file name.
     */
    protected function getMigrationClassNameByFileName(string $file): string
    {
        $fileExploded = explode('_', $file);

        $datePart = implode('_', array_slice($fileExploded, 0, 5));
        $namePart = implode('_', array_slice($fileExploded, 5));

        return Helpers::studly($namePart.'_'.$datePart);
    }

    /**
     * Replace all placeholders in the stub.
     */
    protected function replacePlaceholdersInTemplate(string $template, array $replace): string
    {
        foreach ($replace as $placeholder => $value) {
            $template = str_replace("__{$placeholder}__", $value, $template);
        }

        return $template;
    }

    /**
     * Resolve a migration instance from a file.
     *
     * @throws \Exception
     */
    protected function getMigrationObjectByFileName(string $file): MigrationInterface
    {
        $class = $this->getMigrationClassNameByFileName($file);

        $this->requireMigrationFile($file);

        $object = new $class();

        if (!$object instanceof MigrationInterface) {
            throw new \Exception("Migration class {$class} must implement Uru\\BitrixMigrations\\Interfaces\\MigrationInterface");
        }

        return $object;
    }

    /**
     * Require migration file.
     *
     * @throws \Exception
     */
    protected function requireMigrationFile(string $file)
    {
        $this->files->requireFile($this->getMigrationFilePath($file));
    }

    /**
     * Get path to a migration file.
     *
     * @throws \Exception
     */
    protected function getMigrationFilePath(string $migration): string
    {
        $files = Helpers::rGlob("{$this->dir}/{$migration}.php");
        if (1 != count($files)) {
            throw new \RuntimeException('Not found migration file');
        }

        return $files[0];
    }

    /**
     * @throws \Exception
     */
    protected function checkTransactionAndRun(MigrationInterface $migration, callable $callback): void
    {
        if ($migration->useTransaction($this->use_transaction)) {
            $this->database->startTransaction();
            Logger::log('Начало транзакции', Logger::COLOR_LIGHT_BLUE);

            try {
                $callback();
            } catch (\Exception $e) {
                $this->database->rollbackTransaction();
                Logger::log("Откат транзакции из-за ошибки '{$e->getMessage()}'", Logger::COLOR_LIGHT_RED);

                throw $e;
            }
            $this->database->commitTransaction();
            Logger::log('Конец транзакции', Logger::COLOR_LIGHT_BLUE);
        } else {
            $callback();
        }
    }

    /**
     * If package Uru\BitrixIblockHelper is loaded then we should disable its caching to avoid problems.
     */
    private function disableBitrixIblockHelperCache(): void
    {
        if (class_exists(IblockId::class)) {
            IblockId::setCacheTime(0);
            if (method_exists(IblockId::class, 'flushLocalCache')) {
                IblockId::flushLocalCache();
            }
        }

        if (class_exists('\\Uru\\BitrixIblockHelper\\HLBlock')) {
            HLBlock::setCacheTime(0);
            if (method_exists('\\Uru\\BitrixIblockHelper\\HLBlock', 'flushLocalCache')) {
                HLBlock::flushLocalCache();
            }
        }
    }
}
