<?php

namespace Uru\BitrixIblockHelper;

use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\Application;
use Bitrix\Main\Entity\Base;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\SystemException;
use Uru\BitrixCacher\Cache;

class HLblock
{
    use Cacheable;

    /**
     * Хранилище скомпилированных сущностей для хайлоадблоков.
     */
    protected static array $compiledEntities = [];

    /**
     * Получение данных хайлоадблока по названию его таблицы.
     * Всегда выполняет лишь один запрос в БД на скрипт и возвращает массив вида:
     *
     * array:3 [
     *   "ID" => "2"
     *   "NAME" => "Subscribers"
     *   "TABLE_NAME" => "app_subscribers"
     * ]
     */
    public static function getByTableName(string $table): array
    {
        if (is_null(static::$values)) {
            static::$values = static::getAllByTableNames();
        }

        if (!isset(static::$values[$table])) {
            throw new \RuntimeException("HLBlock for table '{$table}' was not found");
        }

        return static::$values[$table];
    }

    /**
     * Получение ID всех инфоблоков из БД/кэша.
     */
    public static function getAllByTableNames(): array
    {
        $callback = function () {
            $hlBlocks = [];

            $sql = 'SELECT `ID`, `NAME`, `TABLE_NAME` FROM b_hlblock_entity';
            $dbRes = Application::getConnection()->query($sql);
            while ($block = $dbRes->fetch()) {
                $hlBlocks[$block['TABLE_NAME']] = $block;
            }

            return $hlBlocks;
        };

        return static::$cacheMinutes
            ? Cache::remember('arrilot_bih_hlblocks', static::$cacheMinutes, $callback, static::getCacheDir())
            : $callback();
    }

    /**
     * Компилирование и возвращение класса для хайлоадблока для таблицы $table.
     *
     * Пример для таблицы `app_subscribers`:
     * $subscribers = \Uru\BitrixIblockHelper\HLblock::compileClass('app_subscribers');
     * $subscribers::getList();
     *
     * @throws LoaderException|SystemException
     */
    public static function compileClass(string $table): string
    {
        $hldata = static::getByTableName($table);
        static::compileEntity($table);

        return $hldata['NAME'].'Table';
    }

    /**
     * Компилирование сущности для хайлоадблока для таблицы $table.
     * Выполняется один раз.
     *
     * Пример для таблицы `app_subscribers`:
     * $entity = \Uru\BitrixIblockHelper\HLblock::compileEntity('app_subscribers');
     * $query = new Entity\Query($entity);
     *
     * @throws LoaderException|SystemException
     */
    public static function compileEntity(string $table): Base
    {
        if (!isset(static::$compiledEntities[$table])) {
            Loader::includeModule('highloadblock');
            static::$compiledEntities[$table] = HighloadBlockTable::compileEntity(static::getByTableName($table));
        }

        return static::$compiledEntities[$table];
    }

    /**
     * Директория где хранится кэш.
     */
    protected static function getCacheDir(): string
    {
        return '/uru_bih_hlblock';
    }
}
