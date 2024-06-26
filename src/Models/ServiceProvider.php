<?php

namespace Uru\BitrixModels;

use Bitrix\Main\Config\Configuration;
use Bitrix\Main\EventManager;
use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher;
use Illuminate\Pagination\Paginator;
use Uru\BitrixBlade\BladeProvider;
use Uru\BitrixModels\Debug\IlluminateQueryDebugger;
use Uru\BitrixModels\Models\BaseBitrixModel;
use Uru\BitrixModels\Models\EloquentModel;

class ServiceProvider
{
    public static bool $illuminateDatabaseIsUsed = false;

    /**
     * Register the service provider.
     */
    public static function register()
    {
        BaseBitrixModel::setCurrentLanguage(strtoupper(LANGUAGE_ID));
        self::bootstrapIlluminatePagination();
    }

    /**
     * Register eloquent.
     */
    public static function registerEloquent()
    {
        $capsule = self::bootstrapIlluminateDatabase();
        class_alias(Capsule::class, 'DB');

        if ('Y' == $_COOKIE['show_sql_stat']) {
            Capsule::enableQueryLog();

            $em = EventManager::getInstance();
            $em->addEventHandler('main', 'OnAfterEpilog', [IlluminateQueryDebugger::class, 'onAfterEpilogHandler']);
        }

        static::addEventListenersForHelpersHighloadblockTables($capsule);
    }

    /**
     * Bootstrap illuminate/pagination.
     */
    protected static function bootstrapIlluminatePagination()
    {
        if (class_exists(BladeProvider::class)) {
            Paginator::viewFactoryResolver(function () {
                return BladeProvider::getViewFactory();
            });
        }

        Paginator::$defaultView = 'pagination.default';
        Paginator::$defaultSimpleView = 'pagination.simple-default';

        Paginator::currentPathResolver(function () {
            return $GLOBALS['APPLICATION']->getCurPage();
        });

        Paginator::currentPageResolver(function ($pageName = 'page') {
            $page = $_GET[$pageName];

            if (false !== filter_var($page, FILTER_VALIDATE_INT) && (int) $page >= 1) {
                return $page;
            }

            return 1;
        });
    }

    /**
     * Bootstrap illuminate/database.
     */
    protected static function bootstrapIlluminateDatabase(): Capsule
    {
        $capsule = new Capsule(self::instantiateServiceContainer());

        if ($dbConfig = Configuration::getInstance()->get('bitrix-models.illuminate-database')) {
            foreach ($dbConfig['connections'] as $name => $connection) {
                $capsule->addConnection($connection, $name);
            }

            $capsule->getDatabaseManager()->setDefaultConnection((isset($dbConfig['default'])) ? $dbConfig['default'] : 'default');
        } else {
            $config = self::getBitrixDbConfig();

            $capsule->addConnection([
                'driver' => 'mysql',
                'host' => $config['host'],
                'database' => $config['database'],
                'username' => $config['login'],
                'password' => $config['password'],
                'charset' => 'utf8',
                'collation' => 'utf8_unicode_ci',
                'prefix' => '',
                'strict' => false,
            ]);
        }

        if (class_exists(Dispatcher::class)) {
            $capsule->setEventDispatcher(new Dispatcher());
        }

        $capsule->setAsGlobal();
        $capsule->bootEloquent();

        static::$illuminateDatabaseIsUsed = true;

        return $capsule;
    }

    /**
     * Instantiate service container if it's not instantiated yet.
     */
    protected static function instantiateServiceContainer(): Container
    {
        $container = Container::getInstance();

        if (!$container) {
            $container = new Container();
            Container::setInstance($container);
        }

        return $container;
    }

    /**
     * Get bitrix database configuration array.
     */
    protected static function getBitrixDbConfig(): array
    {
        $config = Configuration::getInstance();
        $connections = $config->get('connections');

        return $connections['default'];
    }

    /**
     * Для множественных полей Highload блоков битрикс использует вспомогательные таблицы.
     * Данный метод вешает обработчики на eloquent события добавления и обновления записей которые будут актуализировать и эти таблицы.
     */
    private static function addEventListenersForHelpersHighloadblockTables(Capsule $capsule)
    {
        $dispatcher = $capsule->getEventDispatcher();
        if (!$dispatcher) {
            return;
        }

        $dispatcher->listen(['eloquent.deleted: *'], function ($event, $payload) {
            /** @var EloquentModel $model */
            $model = $payload[0];
            if (empty($model->multipleHighloadBlockFields)) {
                return;
            }

            $modelTable = $model->getTable();
            foreach ($model->multipleHighloadBlockFields as $multipleHighloadBlockField) {
                if (!empty($model['ID'])) {
                    $tableName = $modelTable.'_'.strtolower($multipleHighloadBlockField);
                    \DB::table($tableName)->where('ID', $model['ID'])->delete();
                }
            }
        });

        $dispatcher->listen(['eloquent.updated: *', 'eloquent.created: *'], function ($event, $payload) {
            /** @var EloquentModel $model */
            $model = $payload[0];
            if (empty($model->multipleHighloadBlockFields)) {
                return;
            }

            $dirty = $model->getDirty();
            $modelTable = $model->getTable();
            foreach ($model->multipleHighloadBlockFields as $multipleHighloadBlockField) {
                if (isset($dirty[$multipleHighloadBlockField]) && !empty($model['ID'])) {
                    $tableName = $modelTable.'_'.strtolower($multipleHighloadBlockField);

                    if ('eloquent.updated' === substr($event, 0, 16)) {
                        \DB::table($tableName)->where('ID', $model['ID'])->delete();
                    }

                    $unserializedValues = unserialize($dirty[$multipleHighloadBlockField]);
                    if (!$unserializedValues) {
                        continue;
                    }

                    $newRows = [];
                    foreach ($unserializedValues as $unserializedValue) {
                        $newRows[] = [
                            'ID' => $model['ID'],
                            'VALUE' => $unserializedValue,
                        ];
                    }

                    if ($newRows) {
                        \DB::table($tableName)->insert($newRows);
                    }
                }
            }
        });
    }
}
