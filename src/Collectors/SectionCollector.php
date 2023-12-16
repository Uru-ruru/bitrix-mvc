<?php

namespace Uru\BitrixCollectors;

use Bitrix\Iblock\SectionTable;

/**
 * Class SectionCollector.
 */
class SectionCollector extends OrmTableCollector
{
    protected function entityClassName(): string
    {
        return SectionTable::class;
    }
}
