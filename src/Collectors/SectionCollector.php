<?php

namespace Uru\BitrixCollectors;

use Bitrix\Iblock\SectionTable;

/**
 * Class SectionCollector
 * @package Uru\BitrixCollectors
 */
class SectionCollector extends OrmTableCollector
{
    /**
     * @return string
     */
    protected function entityClassName(): string
    {
        return SectionTable::class;
    }
}
