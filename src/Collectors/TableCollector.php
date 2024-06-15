<?php

namespace Uru\BitrixCollectors;

use Bitrix\Main\Application;
use Bitrix\Main\DB\Connection;

/**
 * Class TableCollector.
 */
abstract class TableCollector extends BitrixCollector
{
    /**
     * Fields that should be selected.
     *
     * @var mixed
     */
    protected $select = ['*'];

    /**
     * Setter for where.
     *
     * @return $this
     */
    public function where(mixed $where): static
    {
        if (!is_string($where)) {
            throw new \LogicException('A string should be passed to `where()` in TableCollector');
        }

        $this->where = $where;

        return $this;
    }

    /**
     * Setter for select.
     *
     * @return $this
     */
    public function select(mixed $select): static
    {
        if (!in_array('ID', $select)) {
            array_unshift($select, 'ID');
        }

        $helper = Application::getConnection()->getSqlHelper();
        foreach ($select as $i => $field) {
            $select[$i] = $helper->quote($field);
        }

        $this->select = $select;

        return $this;
    }

    /**
     * Table name.
     */
    abstract protected function getTableName(): string;

    /**
     * Fetch data for given ids.
     */
    protected function getList(array $ids): array
    {
        $items = [];
        $connection = Application::getConnection();
        $query = $this->buildSqlQuery($ids, $connection);

        $recordset = $connection->query($query);
        while ($el = $recordset->fetchRaw()) {
            $items[$el['ID']] = $el;
        }

        return $items;
    }

    /**
     * Construct sql query to fetch data.
     */
    protected function buildSqlQuery(array $ids, Connection $connection): string
    {
        $idsString = implode(',', $ids);
        $where = count($ids) > 1 ? "ID IN ({$idsString})" : "ID={$ids[0]}";

        if ($this->where) {
            $where .= " AND {$this->where}";
        }

        $select = implode(',', $this->select);
        $table = $connection->getSqlHelper()->quote($this->getTableName());

        return "SELECT {$select} FROM {$table} WHERE {$where}";
    }
}
