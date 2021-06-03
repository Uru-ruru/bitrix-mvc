<?php

namespace Uru\Tests\Collectors\Stubs;

use Uru\BitrixCollectors\Collector;
use RuntimeException;

class FooCollector extends Collector
{
    /**
     * Get data for given ids.
     *
     * @param array $ids
     * @return array
     */
    public function getList(array $ids): array
    {
        if (!$ids) {
            throw new RuntimeException('This line must never be reached.');
        }

        $select = is_null($this->select) ? ['id', 'foo'] : $this->select;

        $data = [];
        foreach ($ids as $id) {
            $data[$id] = array_filter([
                'id'  => $id,
                'foo' => 'bar',
            ], function ($key) use ($select) {
                return in_array($key, $select);
            }, ARRAY_FILTER_USE_KEY);
        }

        return $data;
    }
}
