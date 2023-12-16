<?php

namespace Uru\BitrixMigrations\Constructors;

/**
 * Trait FieldConstructor.
 */
trait FieldConstructor
{
    public array $fields = [];

    public static array $defaultFields = [];

    /**
     * Получить итоговые настройки полей.
     */
    public function getFieldsWithDefault(): array
    {
        return array_merge((array) static::$defaultFields[static::class], $this->fields);
    }
}
