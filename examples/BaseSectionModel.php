<?php

use Uru\BitrixModels\Models\SectionModel;
use Uru\BitrixModels\Queries\SectionQuery;

class BaseSectionModel extends SectionModel
{
    public static $iblockCode = false;

    /**
     * @throws Exception
     */
    public static function iblockId(): int
    {
        if (!static::$iblockCode) {
            throw new \Exception('Необходимо определить public static $iblockCode');
        }

        return Uru\BitrixIblockHelper\IblockId::getByCode(static::$iblockCode);
    }

    public static function baseQuery(): SectionQuery
    {
        return static::query()->sort('SORT')->filter(['ACTIVE' => 'Y']);
    }

    /**
     * Получить название.
     */
    public function getName(): string
    {
        return $this['NAME'];
    }

    /**
     * Получить символьный код.
     */
    public function getCode(): string
    {
        return $this['CODE'];
    }

    /**
     * Проверить активность.
     */
    public function isActive(): bool
    {
        return 'Y' == $this['ACTIVE'];
    }

    /**
     * Проверить идентификатор
     */
    public function getId(): int
    {
        return $this['ID'];
    }
}
