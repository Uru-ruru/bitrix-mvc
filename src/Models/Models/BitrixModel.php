<?php

namespace Uru\BitrixModels\Models;

use Uru\BitrixModels\Exceptions\ExceptionFromBitrix;
use Uru\BitrixModels\Queries\BaseQuery;

abstract class BitrixModel extends BaseBitrixModel
{
    /**
     * Bitrix entity object.
     *
     * @var object
     */
    public static $bxObject;

    /**
     * Fetch method and parameters.
     *
     * @var array|string
     */
    public static $fetchUsing = [
        'method' => 'Fetch',
        'params' => [],
    ];

    /**
     * Corresponding object class name.
     */
    protected static string $objectClass = '';

    /**
     * Constructor.
     *
     * @param null|mixed $id
     * @param null|mixed $fields
     */
    public function __construct($id = null, $fields = null)
    {
        static::instantiateObject();

        $this->id = $id;

        $this->fill($fields);
    }

    /**
     * Activate model.
     *
     * @throws ExceptionFromBitrix
     */
    public function activate(): bool
    {
        $this->fields['ACTIVE'] = 'Y';

        return $this->save(['ACTIVE']);
    }

    /**
     * Deactivate model.
     *
     * @throws ExceptionFromBitrix
     */
    public function deactivate(): bool
    {
        $this->fields['ACTIVE'] = 'N';

        return $this->save(['ACTIVE']);
    }

    public static function internalDirectCreate($bxObject, $fields)
    {
        return $bxObject->add($fields);
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

        $result = static::$bxObject->delete($this->id);

        $this->setEventErrorsOnFail($result, static::$bxObject);
        $this->onAfterDelete($result);
        $this->resetEventErrors();
        $this->throwExceptionOnFail($result, static::$bxObject);

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
        $result = null === $fields || $this->internalUpdate($fields, $fieldsSelectedForSave);

        $this->setEventErrorsOnFail($result, static::$bxObject);
        $this->onAfterUpdate($result);
        $this->onAfterSave($result);
        $this->resetEventErrors();
        $this->throwExceptionOnFail($result, static::$bxObject);

        return $result;
    }

    /**
     * Scope to get only active items.
     */
    public function scopeActive(BaseQuery $query): BaseQuery
    {
        $query->filter['ACTIVE'] = 'Y';

        return $query;
    }

    /**
     * Instantiate bitrix entity object.
     *
     * @throws \LogicException
     */
    public static function instantiateObject(): object
    {
        if (static::$bxObject) {
            return static::$bxObject;
        }

        if (class_exists(static::$objectClass)) {
            return static::$bxObject = new static::$objectClass();
        }

        throw new \LogicException('Object initialization failed');
    }

    /**
     * Destroy bitrix entity object.
     */
    public static function destroyObject(): void
    {
        static::$bxObject = null;
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

        $bxObject = static::instantiateObject();
        $id = static::internalDirectCreate($bxObject, $model->fields);
        $model->setId($id);

        $result = (bool) $id;

        $model->setEventErrorsOnFail($result, $bxObject);
        $model->onAfterCreate($result);
        $model->onAfterSave($result);
        $model->resetEventErrors();
        $model->throwExceptionOnFail($result, $bxObject);

        return $model;
    }

    protected function internalUpdate($fields, $fieldsSelectedForSave): bool
    {
        return !empty($fields) ? static::$bxObject->update($this->id, $fields) : false;
    }

    /**
     * Determine whether the field should be stopped from passing to "update".
     *
     * @param mixed $value
     */
    protected function fieldShouldNotBeSaved(string $field, $value, array $selectedFields): bool
    {
        $blacklistedFields = [
            'ID',
            'IBLOCK_ID',
            'GROUPS',
        ];

        return (!empty($selectedFields) && !in_array($field, $selectedFields))
            || in_array($field, $blacklistedFields)
            || ('~' === $field[0])
            || (0 === strpos($field, 'PROPERTY_'))
            || (is_array($this->original) && array_key_exists($field, $this->original) && $this->original[$field] === $value);
    }

    /**
     * Set eventErrors field on error.
     */
    protected function setEventErrorsOnFail(bool $result, object $bxObject): void
    {
        if (!$result) {
            $this->eventErrors = (array) $bxObject->LAST_ERROR;
        }
    }

    /**
     * Throw bitrix exception on fail.
     *
     * @throws ExceptionFromBitrix
     */
    protected function throwExceptionOnFail(bool $result, object $bxObject): void
    {
        if (!$result) {
            throw new ExceptionFromBitrix($bxObject->LAST_ERROR);
        }
    }
}
