<?php

namespace Uru\BitrixModels\Models;

use BadMethodCallException;
use Exception;
use InvalidArgumentException;
use Uru\BitrixModels\Models\Traits\ModelEventsTrait;
use Uru\BitrixModels\Queries\BaseQuery;
use Illuminate\Support\Collection;
use Illuminate\Support\Arr;
use LogicException;

abstract class BaseBitrixModel extends ArrayableModel
{
    use ModelEventsTrait;

    /**
     * @var string|null
     */
    protected static ?string $currentLanguage = null;

    /**
     * Array of model fields keys that needs to be saved with next save().
     *
     * @var array
     */
    protected array $fieldsSelectedForSave = [];

    /**
     * Array of errors that are passed to model events.
     *
     * @var array
     */
    protected array $eventErrors = [];

    /**
     * Have fields been already fetched from DB?
     *
     * @var bool
     */
    protected bool $fieldsAreFetched = false;

    /**
     * Internal part of create to avoid problems with static and inheritance
     *
     * @param $fields
     *
     * @return static|bool
     * @throws LogicException
     *
     */
    protected static function internalCreate($fields)
    {
        throw new LogicException('internalCreate is not implemented');
    }

    /**
     * Save model to database.
     *
     * @param array $selectedFields save only these fields instead of all.
     *
     * @return bool
     */
    abstract public function save(array $selectedFields = []): bool;

    /**
     * Determine whether the field should be stopped from passing to "update".
     *
     * @param string $field
     * @param mixed $value
     * @param array $selectedFields
     *
     * @return bool
     */
    abstract protected function fieldShouldNotBeSaved(string $field, $value, array $selectedFields): bool;

    /**
     * Get all model attributes from cache or database.
     *
     * @return array
     */
    public function get(): array
    {
        $this->load();

        return $this->fields;
    }

    /**
     * Load model fields from database if they are not loaded yet.
     *
     * @return $this
     */
    public function load(): BaseBitrixModel
    {
        if (!$this->fieldsAreFetched) {
            $this->refresh();
        }

        return $this;
    }

    /**
     * Get model fields from cache or database.
     *
     * @return array
     */
    public function getFields(): array
    {
        if ($this->fieldsAreFetched) {
            return $this->fields;
        }

        return $this->refreshFields();
    }

    /**
     * Refresh model from database and place data to $this->fields.
     *
     * @return array
     */
    public function refresh(): array
    {
        return $this->refreshFields();
    }

    /**
     * Refresh model fields and save them to a class field.
     *
     * @return array
     */
    public function refreshFields(): array
    {
        if ($this->id === null) {
            $this->original = [];
            return $this->fields = [];
        }

        $this->fields = static::query()->getById($this->id)->fields;
        $this->original = $this->fields;

        $this->fieldsAreFetched = true;

        return $this->fields;
    }

    /**
     * Fill model fields if they are already known.
     * Saves DB queries.
     *
     * @param array|null $fields
     *
     * @return void
     */
    public function fill(?array $fields)
    {
        if (!is_array($fields)) {
            return;
        }

        if (isset($fields['ID'])) {
            $this->id = $fields['ID'];
        }

        $this->fields = $fields;

        $this->fieldsAreFetched = true;

        if (method_exists($this, 'afterFill')) {
            $this->afterFill();
        }

        $this->original = $this->fields;
    }

    /**
     * Set current model id.
     *
     * @param $id
     */
    protected function setId($id)
    {
        $this->id = $id;
        $this->fields['ID'] = $id;
    }

    /**
     * Create new item in database.
     *
     * @param $fields
     *
     * @return static|bool
     * @throws LogicException
     *
     */
    public static function create($fields)
    {
        return static::internalCreate($fields);
    }

    /**
     * Get count of items that match $filter.
     *
     * @param array $filter
     *
     * @return int
     */
    public static function count(array $filter = []): int
    {
        return static::query()->filter($filter)->count();
    }

    /**
     * Get item by its id.
     *
     * @param int $id
     *
     * @return static|bool
     */
    public static function find(int $id)
    {
        return static::query()->getById($id);
    }

    /**
     * Update model.
     *
     * @param array $fields
     *
     * @return bool
     */
    public function update(array $fields = []): bool
    {
        $keys = [];
        foreach ($fields as $key => $value) {
            Arr::set($this->fields, $key, $value);
            $keys[] = $key;
        }

        return $this->save($keys);
    }

    /**
     * Create an array of fields that will be saved to database.
     *
     * @param $selectedFields
     *
     * @return array|null
     */
    protected function normalizeFieldsForSave($selectedFields): ?array
    {
        $fields = [];
        if ($this->fields === null) {
            return [];
        }

        foreach ($this->fields as $field => $value) {
            if (!$this->fieldShouldNotBeSaved($field, $value, $selectedFields)) {
                $fields[$field] = $value;
            }
        }

        return $fields ?: null;
    }

    /**
     * Instantiate a query object for the model.
     *
     * @return BaseQuery
     * @throws LogicException
     *
     */
    public static function query()
    {
        throw new LogicException('public static function query() is not implemented');
    }

    /**
     * Handle dynamic static method calls into a new query.
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public static function __callStatic(string $method, array $parameters)
    {
        return static::query()->$method(...$parameters);
    }

    /**
     * Returns the value of a model property.
     *
     * This method will check in the following order and act accordingly:
     *
     *  - a property defined by a getter: return the getter result
     *
     * Do not call this method directly as it is a PHP magic method that
     * will be implicitly called when executing `$value = $component->property;`.
     * @param string $name the property name
     * @return mixed the property value
     * @throws Exception if the property is not defined
     * @see __set()
     */
    public function __get(string $name)
    {
        // Если уже сохранен такой релейшн, то возьмем его
        if (isset($this->related[$name]) || array_key_exists($name, $this->related)) {
            return $this->related[$name];
        }

        // Если нет сохраненных данных, ищем подходящий геттер
        $getter = $name;
        if (method_exists($this, $getter)) {
            // read property, e.g. getName()
            $value = $this->$getter();

            // Если геттер вернул запрос, значит $name - релейшен. Нужно выполнить запрос и сохранить во внутренний массив
            if ($value instanceof BaseQuery) {
                $this->related[$name] = $value->findFor();
                return $this->related[$name];
            }
        }

        throw new Exception('Getting unknown property: ' . get_class($this) . '::' . $name);
    }

    /**
     * Получить запрос для релейшена по имени
     * @param string $name - название релейшена, например `orders` для релейшена, определенного через метод getOrders()
     * @param bool $throwException - кидать ли исключение в случае ошибки
     * @return BaseQuery - запрос для подгрузки релейшена
     * @throws InvalidArgumentException
     */
    public function getRelation(string $name, bool $throwException = true): ?BaseQuery
    {
        $getter = $name;
        try {
            $relation = $this->$getter();
        } catch (BadMethodCallException $e) {
            if ($throwException) {
                throw new InvalidArgumentException(get_class($this) . ' has no relation named "' . $name . '".', 0, $e);
            }

            return null;
        }

        if (!$relation instanceof BaseQuery) {
            if ($throwException) {
                throw new InvalidArgumentException(get_class($this) . ' has no relation named "' . $name . '".');
            }

            return null;
        }

        return $relation;
    }

    /**
     * Reset event errors back to default.
     */
    protected function resetEventErrors()
    {
        $this->eventErrors = [];
    }

    /**
     * Declares a `has-one` relation.
     * The declaration is returned in terms of a relational [[BaseQuery]] instance
     * through which the related record can be queried and retrieved back.
     *
     * A `has-one` relation means that there is at most one related record matching
     * the criteria set by this relation, e.g., a customer has one country.
     *
     * For example, to declare the `country` relation for `Customer` class, we can write
     * the following code in the `Customer` class:
     *
     * ```php
     * public function country()
     * {
     *     return $this->hasOne(Country::className(), 'ID', 'PROPERTY_COUNTRY');
     * }
     * ```
     *
     * Note that in the above, the 'ID' key in the `$link` parameter refers to an attribute name
     * in the related class `Country`, while the 'PROPERTY_COUNTRY' value refers to an attribute name
     * in the current BaseBitrixModel class.
     *
     * Call methods declared in [[BaseQuery]] to further customize the relation.
     *
     * @param string $class the class name of the related record
     * @param string $foreignKey
     * @param string $localKey
     * @return BaseQuery the relational query object.
     */
    public function hasOne(string $class, string $foreignKey, string $localKey = 'ID'): BaseQuery
    {
        return $this->createRelationQuery($class, $foreignKey, $localKey, false);
    }

    /**
     * Declares a `has-many` relation.
     * The declaration is returned in terms of a relational [[BaseQuery]] instance
     * through which the related record can be queried and retrieved back.
     *
     * A `has-many` relation means that there are multiple related records matching
     * the criteria set by this relation, e.g., a customer has many orders.
     *
     * For example, to declare the `orders` relation for `Customer` class, we can write
     * the following code in the `Customer` class:
     *
     * ```php
     * public function orders()
     * {
     *     return $this->hasMany(Order::className(), 'PROPERTY_COUNTRY_VALUE', 'ID');
     * }
     * ```
     *
     * Note that in the above, the 'customer_id' key in the `$link` parameter refers to
     * an attribute name in the related class `Order`, while the 'id' value refers to
     * an attribute name in the current BaseBitrixModel class.
     *
     * Call methods declared in [[BaseQuery]] to further customize the relation.
     *
     * @param string $class the class name of the related record
     * @param string $foreignKey
     * @param string $localKey
     * @return BaseQuery the relational query object.
     */
    public function hasMany(string $class, string $foreignKey, string $localKey = 'ID'): BaseQuery
    {
        return $this->createRelationQuery($class, $foreignKey, $localKey, true);
    }

    /**
     * Creates a query instance for `has-one` or `has-many` relation.
     * @param string $class the class name of the related record.
     * @param string $foreignKey
     * @param string $localKey
     * @param bool $multiple whether this query represents a relation to more than one record.
     * @return BaseQuery the relational query object.
     * @see hasOne()
     * @see hasMany()
     */
    protected function createRelationQuery(string $class, string $foreignKey, string $localKey, bool $multiple): BaseQuery
    {
        /* @var $class BaseBitrixModel */
        $query = $class::query();
        $query->foreignKey = $localKey;
        $query->localKey = $foreignKey;
        $query->primaryModel = $this;
        $query->multiple = $multiple;
        return $query;
    }

    /**
     * Записать модели как связанные
     * @param string $name - название релейшена
     * @param Collection|BaseBitrixModel $records - связанные модели
     * @see getRelation()
     */
    public function populateRelation(string $name, $records)
    {
        $this->related[$name] = $records;
    }

    /**
     * Setter for currentLanguage.
     *
     * @param $language
     * @return void
     */
    public static function setCurrentLanguage($language)
    {
        self::$currentLanguage = $language;
    }

    /**
     * Getter for currentLanguage.
     *
     * @return string
     */
    public static function getCurrentLanguage(): ?string
    {
        return self::$currentLanguage;
    }

    /**
     * Get value from language field according to current language.
     *
     * @param $field
     * @return mixed
     */
    protected function getValueFromLanguageField($field)
    {
        $key = $field . '_' . $this->getCurrentLanguage();

        return $this->fields[$key] ?? null;
    }
}
