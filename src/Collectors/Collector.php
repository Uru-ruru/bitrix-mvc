<?php

namespace Uru\BitrixCollectors;

use Illuminate\Support\Arr;

/**
 * Class Collector.
 */
abstract class Collector
{
    /**
     * Ids that are collected from sources.
     */
    protected array $ids = [];

    /**
     * Fields that should be selected.
     *
     * @var mixed
     */
    protected $select;

    /**
     * Additional filter.
     */
    protected mixed $where = null;

    /**
     * Data keyed by id.
     */
    protected array $data = [];

    /**
     * Add a collection source.
     *
     * @param mixed $fields
     * @param mixed $collection
     *
     * @return $this
     */
    public function scanCollection($collection, $fields): static
    {
        $fields = (array) $fields;
        foreach ($collection as $item) {
            $this->collectIdsFromItem($item, $fields);
        }

        return $this;
    }

    /**
     * Add an item source.
     *
     * @param mixed $item
     *
     * @return $this
     */
    public function scanItem($item, mixed $fields): static
    {
        $fields = (array) $fields;
        $this->collectIdsFromItem($item, $fields);

        return $this;
    }

    /**
     * Add existeing ids array source.
     *
     * @return $this
     */
    public function addIds(array $ids): static
    {
        foreach ($ids as $id) {
            if ((int) $id) {
                $this->ids[] = (int) $id;
            }
        }

        return $this;
    }

    /**
     * Setter for select.
     *
     * @return $this
     */
    public function select(mixed $select): static
    {
        $this->select = $select;

        return $this;
    }

    /**
     * Setter for where.
     *
     * @return $this
     */
    public function where(mixed $where): static
    {
        $this->where = $where;

        return $this;
    }

    /**
     * Perform main query.
     */
    public function performQuery(): array
    {
        if (empty($this->ids)) {
            return [];
        }

        return $this->getList($this->getIdsWithoutDuplications());
    }

    /**
     * Get data for given ids.
     */
    abstract protected function getList(array $ids): array;

    /**
     * Collect ids from source and add them to $this->ids.
     *
     * @param mixed $item
     */
    protected function collectIdsFromItem($item, array $fields): void
    {
        foreach ($fields as $field) {
            foreach ($this->collectIdsFromField($item, $field) as $id) {
                if ((int) $id) {
                    $this->ids[] = (int) $id;
                }
            }
        }
    }

    /**
     * Collect ids from field of item.
     *
     * @param mixed $item
     */
    protected function collectIdsFromField($item, string $field): array
    {
        $ids = Arr::get($item, $field, []);

        return is_object($ids) && method_exists($ids, 'toArray') ? $ids->toArray() : (array) $ids;
    }

    /**
     * A faster alternative to array_unique.
     */
    protected function getIdsWithoutDuplications(): array
    {
        return array_flip(array_flip($this->ids));
    }
}
