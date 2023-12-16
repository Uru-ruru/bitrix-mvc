<?php

namespace Uru\BitrixCollectors;

/**
 * Class BitrixCollector.
 */
abstract class BitrixCollector extends Collector
{
    /**
     * Fields that should be selected.
     *
     * @var mixed
     */
    protected $select = [];

    /**
     * Setter for select.
     *
     * @return $this
     */
    public function select(mixed $select)
    {
        if (!in_array('ID', $select)) {
            array_unshift($select, 'ID');
        }

        $this->select = $select;

        return $this;
    }

    /**
     * Build filter.
     */
    protected function buildFilter(array $ids): array
    {
        $filter = count($ids) > 1 ? ['=ID' => $ids] : ['=ID' => $ids[0]];

        if (!empty($this->where) && is_array($this->where)) {
            $filter = array_merge($filter, $this->where);
        }

        return $filter;
    }
}
