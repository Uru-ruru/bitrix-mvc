<?php

namespace Uru\BitrixCollectors;

/**
 * Class ElementCollector.
 */
class ElementCollector extends BitrixCollector
{
    /**
     * Fetch data for given ids.
     */
    protected function getList(array $ids): array
    {
        $items = [];
        $res = \CIBlockElement::GetList(['ID' => 'ASC'], $this->buildFilter($ids), false, false, $this->select);
        while ($el = $res->Fetch()) {
            $items[$el['ID']] = $el;
        }

        return $items;
    }
}
