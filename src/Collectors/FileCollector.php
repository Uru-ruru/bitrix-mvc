<?php

namespace Uru\BitrixCollectors;

use Bitrix\Main\FileTable;

/**
 * Class FileCollector.
 */
class FileCollector extends OrmTableCollector
{
    protected function entityClassName(): string
    {
        return FileTable::class;
    }

    protected function transformItems(array $items): array
    {
        foreach ($items as $id => $item) {
            $items[$id]['PATH'] = "/upload/{$item['SUBDIR']}/{$item['FILE_NAME']}";
        }

        return $items;
    }
}
