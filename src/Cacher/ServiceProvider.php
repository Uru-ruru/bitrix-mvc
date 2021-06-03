<?php

namespace Uru\BitrixCacher;

use Bitrix\Main\EventManager;
use Uru\BitrixCacher\Debug\CacheDebugger;

/**
 * Class ServiceProvider
 * @package Uru\BitrixCacher
 */
class ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public static function register()
    {
        $em = EventManager::getInstance();
        $em->addEventHandler('main', 'OnAfterEpilog', [CacheDebugger::class, 'onAfterEpilogHandler']);
    }
}
