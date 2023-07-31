<?php

namespace Uru\BitrixMigrations\Autocreate;

use Uru\BitrixMigrations\Autocreate\Handlers\HandlerInterface;
use Uru\BitrixMigrations\Exceptions\SkipHandlerException;
use Uru\BitrixMigrations\Exceptions\StopHandlerException;
use Uru\BitrixMigrations\Migrator;
use Uru\BitrixMigrations\TemplatesCollection;
use Bitrix\Main\Entity\EventResult;
use Bitrix\Main\EventManager;

class Manager
{
    /**
     * A flag that autocreation was turned on.
     *
     * @var bool
     */
    protected static bool $isTurnedOn = false;

    /**
     * @var Migrator
     */
    protected static Migrator $migrator;

    /**
     * Handlers that are used by autocreation.
     *
     * @var array
     */
    protected static array $handlers = [
        'iblock' => [
            'OnBeforeIBlockAdd'            => 'OnBeforeIBlockAdd',
            'OnBeforeIBlockUpdate'         => 'OnBeforeIBlockUpdate',
            'OnBeforeIBlockDelete'         => 'OnBeforeIBlockDelete',
            'OnBeforeIBlockPropertyAdd'    => 'OnBeforeIBlockPropertyAdd',
            'OnBeforeIBlockPropertyUpdate' => 'OnBeforeIBlockPropertyUpdate',
            'OnBeforeIBlockPropertyDelete' => 'OnBeforeIBlockPropertyDelete',
        ],
        'main' => [
            'OnBeforeUserTypeAdd'    => 'OnBeforeUserTypeAdd',
            'OnBeforeUserTypeDelete' => 'OnBeforeUserTypeDelete',
            'OnBeforeGroupAdd'       => 'OnBeforeGroupAdd',
            'OnBeforeGroupUpdate'    => 'OnBeforeGroupUpdate',
            'OnBeforeGroupDelete'    => 'OnBeforeGroupDelete',
        ],
        'highloadblock' => [
            '\\Bitrix\\Highloadblock\\Highloadblock::OnBeforeAdd'    => 'OnBeforeHLBlockAdd',
            '\\Bitrix\\Highloadblock\\Highloadblock::OnBeforeUpdate' => 'OnBeforeHLBlockUpdate',
            '\\Bitrix\\Highloadblock\\Highloadblock::OnBeforeDelete' => 'OnBeforeHLBlockDelete',
        ],
    ];

    /**
     * Initialize autocreation.
     *
     * @param string $dir
     * @param string|null $table
     */
    public static function init(string $dir, ?string $table = null): void
    {
        $templates = new TemplatesCollection();
        $templates->registerAutoTemplates();

        $config = [
            'dir'   => $dir,
            'table' => is_null($table) ? 'migrations' : $table,
        ];

        static::$migrator = new Migrator($config, $templates);

        static::addEventHandlers();

        static::turnOn();
    }

    /**
     * Determine if autocreation is turned on.
     *
     * @return bool
     */
    public static function isTurnedOn(): bool
    {
        return static::$isTurnedOn && defined('ADMIN_SECTION');
    }

    /**
     * Turn on autocreation.
     *
     * @return void
     */
    public static function turnOn(): void
    {
        static::$isTurnedOn = true;
    }

    /**
     * Turn off autocreation.
     *
     * @return void
     */
    public static function turnOff(): void
    {
        static::$isTurnedOn = false;
    }

    /**
     * Instantiate handler.
     *
     * @param string $handler
     * @param array $parameters
     *
     * @return mixed
     */
    protected static function instantiateHandler(string $handler, array $parameters): mixed
    {
        $class = __NAMESPACE__.'\\Handlers\\'.$handler;

        return new $class($parameters);
    }

    /**
     * Create migration and apply it.
     *
     * @param HandlerInterface $handler
     * @throws \Exception
     */
    protected static function createMigration(HandlerInterface $handler): void
    {
        $migrator = static::$migrator;
        $notifier = new Notifier();

        $migration = $migrator->createMigration(
            strtolower($handler->getName()),
            $handler->getTemplate(),
            $handler->getReplace()
        );

        $migrator->logSuccessfulMigration($migration);
        $notifier->newMigration($migration);
    }

    /**
     * Add event handlers.
     */
    protected static function addEventHandlers(): void
    {
        $eventManager = EventManager::getInstance();

        foreach (static::$handlers as $module => $handlers) {
            foreach ($handlers as $event => $handler) {
                $eventManager->addEventHandler($module, $event, [__CLASS__, $handler], false, 5000);
            }
        }

        $eventManager->addEventHandler('main', 'OnAfterEpilog', function () {
            $notifier = new Notifier();
            $notifier->deleteNotificationFromPreviousMigration();

            return new EventResult();
        });
    }

    /**
     * Magic static call to a handler.
     *
     * @param string $method
     * @param array $parameters
     *
     * @return mixed
     * @throws \Exception
     */
    public static function __callStatic($method, $parameters)
    {
        $eventResult = new EventResult();

        if (!static::isTurnedOn()) {
            return $eventResult;
        }

        try {
            $handler = static::instantiateHandler($method, $parameters);
        } catch (SkipHandlerException $e) {
            return $eventResult;
        } catch (StopHandlerException $e) {
            global $APPLICATION;
            $APPLICATION->throwException($e->getMessage());

            return false;
        }

        static::createMigration($handler);

        return $eventResult;
    }
}
