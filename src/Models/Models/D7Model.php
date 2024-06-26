<?php

namespace Uru\BitrixModels\Models;

use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\Entity\Result;
use Bitrix\Main\Entity\UpdateResult;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Uru\BitrixModels\Adapters\D7Adapter;
use Uru\BitrixModels\Exceptions\ExceptionFromBitrix;
use Uru\BitrixModels\Models\Interfaces\ResultInterface;
use Uru\BitrixModels\Queries\D7Query;

/**
 * static int count().
 *
 * D7Query methods
 *
 * @method static D7Query runtime(array|ExpressionField $fields)
 * @method static D7Query enableDataDoubling()
 * @method static D7Query disableDataDoubling()
 * @method static D7Query cacheJoins(bool $value)
 *
 * BaseQuery methods
 * @method static Collection|static[]  getList()
 * @method static static               first()
 * @method static static               getById(int $id)
 * @method static D7Query              sort(string|array $by, string $order = 'ASC')
 * @method static D7Query              order(string|array $by, string $order = 'ASC') // same as sort()
 * @method static D7Query              filter(array $filter)
 * @method static D7Query              addFilter(array $filters)
 * @method static D7Query              resetFilter()
 * @method static D7Query              navigation(array $filter)
 * @method static D7Query              select($value)
 * @method static D7Query              keyBy(string $value)
 * @method static D7Query              limit(int $value)
 * @method static D7Query              offset(int $value)
 * @method static D7Query              page(int $num)
 * @method static D7Query              take(int $value) // same as limit()
 * @method static D7Query              forPage(int $page, int $perPage = 15)
 * @method static LengthAwarePaginator paginate(int $perPage = 15, string $pageName = 'page')
 * @method static Paginator            simplePaginate(int $perPage = 15, string $pageName = 'page')
 * @method static D7Query              stopQuery()
 * @method static D7Query              cache(float|int $minutes)
 */
class D7Model extends BaseBitrixModel
{
    public const TABLE_CLASS = null;

    /**
     * @var null|string
     */
    protected static $cachedTableClasses = [];

    /**
     * Array of adapters for each model to interact with Bitrix D7 API.
     *
     * @var D7Adapter[]
     */
    protected static $adapters = [];

    /**
     * Constructor.
     *
     * @param null|mixed $id
     * @param null|mixed $fields
     */
    public function __construct($id = null, $fields = null)
    {
        $this->id = $id;
        $this->fill($fields);
        static::instantiateAdapter();
    }

    /**
     * Setter for adapter (for testing).
     *
     * @param mixed $adapter
     */
    public static function setAdapter($adapter)
    {
        static::$adapters[static::class] = $adapter;
    }

    /**
     * Instantiate adapter if it's not instantiated.
     */
    public static function instantiateAdapter(): D7Adapter
    {
        $class = static::class;

        return static::$adapters[$class] ?? (static::$adapters[$class] = new D7Adapter(static::cachedTableClass()));
    }

    /**
     * Instantiate a query object for the model.
     */
    public static function query(): D7Query
    {
        return new D7Query(static::instantiateAdapter(), static::class);
    }

    /**
     * @throws \LogicException
     */
    public static function tableClass(): string
    {
        $tableClass = static::TABLE_CLASS;
        if (!$tableClass) {
            throw new \LogicException('You must set TABLE_CLASS constant inside a model or override tableClass() method');
        }

        return $tableClass;
    }

    /**
     * Cached version of table class.
     */
    public static function cachedTableClass(): string
    {
        $class = static::class;
        if (!isset(static::$cachedTableClasses[$class])) {
            static::$cachedTableClasses[$class] = static::tableClass();
        }

        return static::$cachedTableClasses[$class];
    }

    /**
     * Delete model.
     *
     * @throws ExceptionFromBitrix
     */
    public function delete(): bool
    {
        if (false === $this->onBeforeDelete()) {
            return false;
        }

        $resultObject = static::instantiateAdapter()->delete($this->id);
        $result = $resultObject->isSuccess();

        $this->setEventErrorsOnFail($resultObject);
        $this->onAfterDelete($result);
        $this->resetEventErrors();
        $this->throwExceptionOnFail($resultObject);

        return $result;
    }

    /**
     * Save model to database.
     *
     * @param array $selectedFields save only these fields instead of all
     *
     * @throws ExceptionFromBitrix
     */
    public function save(array $selectedFields = []): bool
    {
        $fieldsSelectedForSave = is_array($selectedFields) ? $selectedFields : func_get_args();
        $this->fieldsSelectedForSave = $fieldsSelectedForSave;
        if (false === $this->onBeforeSave() || false === $this->onBeforeUpdate()) {
            $this->fieldsSelectedForSave = [];

            return false;
        }

        $this->fieldsSelectedForSave = [];

        $fields = $this->normalizeFieldsForSave($fieldsSelectedForSave);
        $resultObject = null === $fields
            ? new UpdateResult()
            : static::instantiateAdapter()->update($this->id, $fields);
        $result = $resultObject->isSuccess();

        $this->setEventErrorsOnFail($resultObject);
        $this->onAfterUpdate($result);
        $this->onAfterSave($result);
        $this->throwExceptionOnFail($resultObject);

        return $result;
    }

    /**
     * Internal part of create to avoid problems with static and inheritance.
     *
     * @param mixed $fields
     *
     * @return bool|static
     *
     * @throws ExceptionFromBitrix
     */
    protected static function internalCreate($fields)
    {
        $model = new static(null, $fields);

        if (false === $model->onBeforeSave() || false === $model->onBeforeCreate()) {
            return false;
        }

        $resultObject = static::instantiateAdapter()->add($model->fields);
        $result = $resultObject->isSuccess();
        if ($result) {
            $model->setId($resultObject->getId());
        }

        $model->setEventErrorsOnFail($resultObject);
        $model->onAfterCreate($result);
        $model->onAfterSave($result);
        $model->throwExceptionOnFail($resultObject);

        return $model;
    }

    /**
     * Determine whether the field should be stopped from passing to "update".
     *
     * @param mixed $value
     */
    protected function fieldShouldNotBeSaved(string $field, $value, array $selectedFields): bool
    {
        return (!empty($selectedFields) && !in_array($field, $selectedFields)) || 'ID' === $field;
    }

    /**
     * Throw bitrix exception on fail.
     *
     * @throws ExceptionFromBitrix
     */
    protected function throwExceptionOnFail(Result|ResultInterface $resultObject): void
    {
        if (!$resultObject->isSuccess()) {
            throw new ExceptionFromBitrix(implode('; ', $resultObject->getErrorMessages()));
        }
    }

    /**
     * Set eventErrors field on error.
     */
    protected function setEventErrorsOnFail(Result|ResultInterface $resultObject): void
    {
        if (!$resultObject->isSuccess()) {
            $this->eventErrors = $resultObject->getErrorMessages();
        }
    }
}
