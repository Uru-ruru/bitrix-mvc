<?php

namespace Uru\BitrixModels\Queries;

use Illuminate\Support\Collection;
use Uru\BitrixModels\Adapters\D7Adapter;

class D7Query extends BaseQuery
{
    /**
     * Query select.
     */
    public array $select = ['*'];

    /**
     * Query group by.
     */
    public array $group = [];

    /**
     * Query runtime.
     */
    public array $runtime = [];

    /**
     * Query limit.
     */
    public ?int $limit = null;

    /**
     * Query offset.
     */
    public ?int $offset = null;

    /**
     * Cache joins?
     */
    public bool $cacheJoins = false;

    /**
     * Data doubling?
     */
    public bool $dataDoubling = true;

    /**
     * Adapter to interact with Bitrix D7 API.
     *
     * @var D7Adapter
     */
    protected $bxObject;

    /**
     * Get count of users that match $filter.
     */
    public function count(): int
    {
        $className = $this->bxObject->getClassName();
        $queryType = 'D7Query::count';
        $filter = $this->filter;

        $callback = function () use ($filter) {
            return (int) $this->bxObject->getCount($filter);
        };

        return $this->handleCacheIfNeeded(compact('className', 'filter', 'queryType'), $callback);
    }

    /**
     * Setter for limit.
     *
     * @param null|int $value
     *
     * @return $this
     */
    public function limit($value)
    {
        $this->limit = $value;

        return $this;
    }

    /**
     * Setter for offset.
     *
     * @return $this
     */
    public function offset(?int $value)
    {
        $this->offset = $value;

        return $this;
    }

    /**
     * Set the "page number" value of the query.
     *
     * @return $this
     */
    public function page(int $num)
    {
        return $this->offset((int) $this->limit * ($num - 1));
    }

    /**
     * Setter for offset.
     *
     * @param array|\Bitrix\Main\Entity\ExpressionField $fields
     *
     * @return $this
     */
    public function runtime($fields)
    {
        $this->runtime = is_array($fields) ? $fields : [$fields];

        return $this;
    }

    /**
     * Setter for cacheJoins.
     *
     * @return $this
     */
    public function cacheJoins(bool $value = true)
    {
        $this->cacheJoins = $value;

        return $this;
    }

    public function enableDataDoubling()
    {
        $this->dataDoubling = true;

        return $this;
    }

    public function disableDataDoubling()
    {
        $this->dataDoubling = false;

        return $this;
    }

    /**
     * For testing.
     *
     * @param mixed $bxObject
     *
     * @return $this
     */
    public function setAdapter($bxObject)
    {
        $this->bxObject = $bxObject;

        return $this;
    }

    /**
     * Get list of items.
     *
     * @return Collection
     */
    protected function loadModels()
    {
        $params = [
            'select' => $this->select,
            'filter' => $this->filter,
            'group' => $this->group,
            'order' => $this->sort,
            'limit' => $this->limit,
            'offset' => $this->offset,
            'runtime' => $this->runtime,
        ];

        if ($this->cacheTtl && $this->cacheJoins) {
            $params['cache'] = ['ttl' => $this->cacheTtl, 'cache_joins' => true];
        }

        $className = $this->bxObject->getClassName();
        $queryType = 'D7Query::getList';
        $keyBy = $this->keyBy;

        $callback = function () use ($params) {
            $rows = [];
            $result = $this->bxObject->getList($params);
            while ($row = $result->fetch()) {
                $this->addItemToResultsUsingKeyBy($rows, new $this->modelName($row['ID'], $row));
            }

            return new Collection($rows);
        };

        return $this->handleCacheIfNeeded(compact('className', 'params', 'queryType', 'keyBy'), $callback);
    }
}
