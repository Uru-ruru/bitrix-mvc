<?php

namespace Uru\BitrixMigrations;

use Bitrix\Main\Application;

/**
 * Class Helpers.
 */
class Helpers
{
    protected static array $hls = [];

    protected static array $ufs = [];

    /**
     * Convert a value to studly caps case.
     */
    public static function studly(string $value): string
    {
        $value = ucwords(str_replace(['-', '_'], ' ', $value));

        return str_replace(' ', '', $value);
    }

    /**
     * Рекурсивный поиск миграций с поддирректориях.
     *
     * @param int   $flags   Does not support flag GLOB_BRACE
     * @param mixed $pattern
     */
    public static function rGlob($pattern, int $flags = 0): array
    {
        $files = glob($pattern, $flags);
        foreach (glob(dirname($pattern).'/*', GLOB_ONLYDIR | GLOB_NOSORT) as $dir) {
            $files = array_merge($files, static::rGlob($dir.'/'.basename($pattern), $flags));
        }

        return $files;
    }

    /**
     * Получить ID HL по названию таблицы.
     *
     * @param mixed $table_name
     */
    public static function getHlId($table_name): mixed
    {
        if (!isset(static::$hls[$table_name])) {
            $dbRes = Application::getConnection()->query('SELECT `ID`, `NAME`, `TABLE_NAME` FROM b_hlblock_entity');
            while ($block = $dbRes->fetch()) {
                static::$hls[$block['TABLE_NAME']] = $block;
            }
        }

        return static::$hls[$table_name]['ID'];
    }

    /**
     * Получить ID UF.
     *
     * @param mixed $obj
     * @param mixed $field_name
     *
     * @return mixed
     */
    public static function getFieldId($obj, $field_name)
    {
        if (!isset(static::$ufs[$obj][$field_name])) {
            $dbRes = Application::getConnection()->query('SELECT * FROM b_user_field');
            while ($uf = $dbRes->fetch()) {
                static::$ufs[$uf['ENTITY_ID']][$uf['FIELD_NAME']] = $uf;
            }
        }

        return static::$ufs[$obj][$field_name]['ID'];
    }
}
