<?php

namespace Uru\BitrixCacher\Debug;

use Bitrix\Main\Data\Cache;

/**
 * Class CacheDebugger.
 */
class CacheDebugger
{
    protected static array $cacheTracks;

    public static function track($name, $initDir, $basedir, $key, $result): void
    {
        $size = strlen(@serialize($result));
        static::$cacheTracks[] = compact('name', 'initDir', 'basedir', 'key', 'size');
    }

    /**
     * @param mixed $name
     *
     * @throws \JsonException
     */
    public static function getCacheTracksGrouped($name): array
    {
        $tracks = [];
        foreach (static::$cacheTracks as $track) {
            if ($track['name'] !== $name) {
                continue;
            }

            $hash = json_encode([$track['key'], $track['initDir'], $track['basedir']], JSON_THROW_ON_ERROR);
            if (isset($tracks[$hash])) {
                ++$tracks[$hash]['count'];
                $tracks[$hash]['size'] += $track['size'];
            } else {
                $tracks[$hash] = $track;
                $tracks[$hash]['count'] = 1;
            }
        }

        return $tracks;
    }

    public static function getTracksCount(): int
    {
        return count(static::$cacheTracks);
    }

    public static function onAfterEpilogHandler(): void
    {
        global $USER;

        $bExcel = isset($_REQUEST['mode']) && 'excel' === $_REQUEST['mode'];
        if (!defined('ADMIN_AJAX_MODE') && !defined('PUBLIC_AJAX_MODE') && !$bExcel) {
            $bShowCacheStat = (Cache::getShowCacheStat() && ($USER->CanDoOperation('edit_php') || 'Y' === $_SESSION['SHOW_CACHE_STAT']));
            if ($bShowCacheStat) {
                require_once __DIR__.'/debug_info.php';
            }
        }
    }
}
