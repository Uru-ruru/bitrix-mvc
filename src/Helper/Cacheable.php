<?php

namespace Uru\BitrixIblockHelper;

use CPHPCache;

trait Cacheable
{
    /**
     * Хранилище полученных из базы ID.
     *
     * @var array|null
     */
    protected static ?array $values;

    /**
     * Время кэширования списка.
     *
     * @var float|int
     */
    protected static $cacheMinutes = 0;

    /**
     * Директория где хранится кэш.
     *
     * @return string
     */
    protected static function getCacheDir(): string
    {
        return '/uru_bih';
    }

    /**
     * Setter for $cacheMinutes
     *
     * @param $minutes
     */
    public static function setCacheTime($minutes)
    {
        static::$cacheMinutes = $minutes;
    }

    /**
     * Flushes local cache
     */
    public static function flushLocalCache()
    {
        static::$values = null;
    }

    /**
     * Flushes local cache
     */
    public static function flushExternalCache()
    {
        (new CPHPCache())->CleanDir(static::getCacheDir());
    }
}
