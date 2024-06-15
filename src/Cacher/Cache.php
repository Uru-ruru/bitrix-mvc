<?php

namespace Uru\BitrixCacher;

use Bitrix\Main\Data\StaticHtmlCache;
use Closure;
use Uru\BitrixCacher\Debug\CacheDebugger;

/**
 * Class Cache.
 */
class Cache
{
    /**
     * Store closure's result in the cache for a given number of minutes.
     */
    public static function remember(string $key, float $minutes, \Closure $callback, bool|string $initDir = '/', string $basedir = 'cache'): mixed
    {
        $debug = \Bitrix\Main\Data\Cache::getShowCacheStat();

        if ($minutes <= 0) {
            try {
                $result = $callback();
            } catch (AbortCacheException $e) {
                $result = null;
            }

            if ($debug) {
                CacheDebugger::track('zero_ttl', $initDir, $basedir, $key, $result);
            }

            return $result;
        }

        $obCache = new \CPHPCache();
        if ($obCache->InitCache($minutes * 60, $key, $initDir, $basedir)) {
            $vars = $obCache->GetVars();

            if ($debug) {
                CacheDebugger::track('hits', $initDir, $basedir, $key, $vars['cache']);
            }

            return $vars['cache'];
        }

        $obCache->StartDataCache();

        try {
            $cache = $callback();
            $obCache->EndDataCache(['cache' => $cache]);
        } catch (AbortCacheException $e) {
            $obCache->AbortDataCache();
            $cache = null;
        }

        if ($debug) {
            CacheDebugger::track('misses', $initDir, $basedir, $key, $cache);
        }

        return $cache;
    }

    /**
     * Store closure's result in the cache for a long time.
     */
    public static function rememberForever(string $key, \Closure $callback, bool|string $initDir = '/', string $basedir = 'cache'): mixed
    {
        return static::remember($key, 99999999, $callback, $initDir, $basedir);
    }

    /**
     * Flush cache for a specified dir.
     */
    public static function flush(string $initDir = ''): bool
    {
        return BXClearCache(true, $initDir);
    }

    /**
     * Flushes all bitrix cache.
     */
    public static function flushAll(): void
    {
        $GLOBALS['CACHE_MANAGER']->cleanAll();
        $GLOBALS['stackCacheManager']->cleanAll();
        $staticHtmlCache = StaticHtmlCache::getInstance();
        if ($staticHtmlCache) {
            $staticHtmlCache->deleteAll();
        }
        BXClearCache(true);
    }
}
