<?php

namespace Uru\BitrixCollectors;


use CIBlockElement;

/**
 * Class ElementCollector
 * @package Uru\BitrixCollectors
 */
class ElementCollector extends BitrixCollector
{
    /**
     * Fetch data for given ids.
     *
     * @param array $ids
     * @return array
     */
    protected function getList(array $ids): array
    {
        $items = [];
        $res = CIBlockElement::GetList(["ID" => "ASC"], $this->buildFilter($ids), false, false, $this->select);
        while ($el = $res->Fetch()) {
            $items[$el['ID']] = $el;
        }

        return $items;
    }
}
