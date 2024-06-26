<?php

namespace Uru\BitrixHLBlockFieldsFixer;

use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\Application;

class Fixer
{
    protected static array $newFieldTypes = [
        'string' => 'varchar(255)',
        'string_formatted' => 'varchar(255)',
        'text' => 'text',
        'boolean' => 'tinyint(1)',
    ];

    public static function setNewFieldType($field, $type): void
    {
        static::$newFieldTypes[$field] = $type;
    }

    /**
     * @param mixed $field
     *
     * @return null|mixed
     */
    public static function getNewFieldType($field): mixed
    {
        return static::$newFieldTypes[$field] ?? null;
    }

    /**
     * Main handler.
     *
     * @param mixed $field
     */
    public static function adjustFieldInDatabaseOnAfterUserTypeAdd($field): bool
    {
        if (!preg_match('/^HLBLOCK_(\d+)$/', $field['ENTITY_ID'], $matches)) {
            return true;
        }

        // множественные не трогаем
        if ('Y' === $field['MULTIPLE']) {
            return true;
        }

        $connection = Application::getConnection();
        $sqlHelper = $connection->getSqlHelper();

        $hlblock_id = $matches[1];
        $hlblock = HighloadBlockTable::getById($hlblock_id)->fetch();
        if (empty($hlblock)) {
            return true;
        }

        $settings = unserialize($field['SETTINGS'], ['allowed_classes' => false]);
        $sqlTableName = $sqlHelper->quote($hlblock['TABLE_NAME']);
        $sqlFieldName = $sqlHelper->quote($field['FIELD_NAME']);

        $type = $field['USER_TYPE_ID'];
        if (('string' === $type || 'string_formatted' === $type) && $settings['ROWS'] > 1) {
            $type = 'text';
        }

        $newType = static::getNewFieldType($type);
        if ($newType) {
            $connection->query(sprintf('ALTER TABLE %s MODIFY COLUMN %s %s', $sqlTableName, $sqlFieldName, $newType));
        }

        return true;
    }
}
