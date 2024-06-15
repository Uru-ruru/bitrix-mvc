<?php

namespace Uru\BitrixCacher;

use Bitrix\Main\EventManager;
use Uru\BitrixCacher\Debug\CacheDebugger;

/**
 * Class ServiceProvider.
 */
class ServiceProvider
{
    /**
     * Register the service provider.
     */
    public static function register(): void
    {
        $em = EventManager::getInstance();
        $em->addEventHandler('main', 'OnAfterEpilog', [CacheDebugger::class, 'onAfterEpilogHandler']);
    }
}
