<?php

namespace Uru\BitrixCollectors;

use Bitrix\Main\UserTable;

/**
 * Class UserCollector
 * @package Uru\BitrixCollectors
 */
class UserCollector extends OrmTableCollector
{
    /**
     * @return string
     */
    protected function entityClassName(): string
    {
        return UserTable::class;
    }
}
