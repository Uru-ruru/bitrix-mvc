<?php

namespace Uru\BitrixHLBlockFieldsFixer;

use Bitrix\Main\EventManager;

class ServiceProvider
{
    public static function register($config = [])
    {
        foreach ($config as $bitrixField => $mysqlField) {
            Fixer::setNewFieldType($bitrixField, $mysqlField);
        }

        $em = EventManager::getInstance();
        $em->addEventHandler('main', 'OnAfterUserTypeAdd', [Fixer::class, "adjustFieldInDatabaseOnAfterUserTypeAdd"], false, 101);
    }
}
