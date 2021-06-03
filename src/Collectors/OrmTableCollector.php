<?php

namespace Uru\BitrixCollectors;

/**
 * Class OrmTableCollector
 * @package Uru\BitrixCollectors
 */
abstract class OrmTableCollector extends BitrixCollector
{
    /**
     * Fields that should be selected.
     *
     * @var mixed
     */
    protected $select = ['*'];

    /**
     * Class name of the entity.
     *
     * @return string
     */
    abstract protected function entityClassName(): string;

    /**
     * Fetch data for given ids.
     *
     * @param array $ids
     * @return array
     */
    protected function getList(array $ids): array
    {
        $items = [];
        $entity = $this->entityClassName();
        $result = $entity::getList([
            'filter' => $this->buildFilter($ids),
            'select' => $this->select,
        ]);
        while ($row = $result->fetch()) {
            $items[$row['ID']] = $row;
        }

        return $this->transformItems($items);
    }

    /**
     * Transform items after fetch.
     *
     * @param array $items
     * @return array
     */
    protected function transformItems(array $items): array
    {
        return $items;
    }
}
