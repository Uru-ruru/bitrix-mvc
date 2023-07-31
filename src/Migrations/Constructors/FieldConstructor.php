<?php


namespace Uru\BitrixMigrations\Constructors;


/**
 * Trait FieldConstructor
 * @package Uru\BitrixMigrations\Constructors
 */
trait FieldConstructor
{
    /** @var array */
    public array $fields = [];

    /**
     * @var array
     */
    public static array $defaultFields = [];

    /**
     * Получить итоговые настройки полей
     */
    public function getFieldsWithDefault(): array
    {
        return array_merge((array)static::$defaultFields[static::class], $this->fields);
    }
}
