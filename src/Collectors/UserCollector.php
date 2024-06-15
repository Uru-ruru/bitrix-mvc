<?php

namespace Uru\BitrixCollectors;

use Bitrix\Main\UserTable;

/**
 * Class UserCollector.
 */
class UserCollector extends OrmTableCollector
{
    protected function entityClassName(): string
    {
        return UserTable::class;
    }
}
