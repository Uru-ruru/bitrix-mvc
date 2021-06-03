<?php

namespace Uru\BitrixCollectors;

use Bitrix\Main\FileTable;

/**
 * Class FileCollector
 * @package Uru\BitrixCollectors
 */
class FileCollector extends OrmTableCollector
{
    /**
     * @return string
     */
    protected function entityClassName(): string
    {
        return FileTable::class;
    }

    /**
     * @param array $items
     * @return array
     */
    protected function transformItems(array $items): array
    {
        foreach ($items as $id => $item) {
             $items[$id]['PATH'] = "/upload/{$item['SUBDIR']}/{$item['FILE_NAME']}";
        }

        return $items;
    }
}
