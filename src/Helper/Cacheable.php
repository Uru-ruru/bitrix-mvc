<?php

namespace Uru\BitrixIblockHelper;

trait Cacheable
{
    /**
     * Хранилище полученных из базы ID.
     */
    protected static ?array $values = null;

    /**
     * Время кэширования списка.
     */
    protected static float|int $cacheMinutes = 0;

    /**
     * Setter for $cacheMinutes.
     *
     * @param mixed $minutes
     */
    public static function setCacheTime($minutes): void
    {
        static::$cacheMinutes = $minutes;
    }

    /**
     * Flushes local cache.
     */
    public static function flushLocalCache(): void
    {
        static::$values = null;
    }

    /**
     * Flushes local cache.
     */
    public static function flushExternalCache(): void
    {
        (new \CPHPCache())->CleanDir(static::getCacheDir());
    }

    /**
     * Директория где хранится кэш.
     */
    protected static function getCacheDir(): string
    {
        return '/uru_bih';
    }
}
