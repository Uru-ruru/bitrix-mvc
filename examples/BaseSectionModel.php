<?php

use Uru\BitrixModels\Queries\SectionQuery;
use Uru\BitrixModels\Models\SectionModel;

class BaseSectionModel extends SectionModel
{
    public static $iblockCode = false;

    /**
     * @throws Exception
     */
    public static function iblockId(): int
    {
        if (!static::$iblockCode) {
            throw new \Exception("Необходимо определить public static \$iblockCode");
        }

        return Uru\BitrixIblockHelper\IblockId::getByCode(static::$iblockCode);
    }

    /**
     * @return SectionQuery
     */
    public static function baseQuery(): SectionQuery
    {
        return static::query()->sort('SORT')->filter(['ACTIVE' => 'Y']);
    }

    /**
     * Получить название
     * @return string
     */
    public function getName(): string
    {
        return $this['NAME'];
    }

    /**
     * Получить символьный код
     * @return string
     */
    public function getCode(): string
    {
        return $this['CODE'];
    }

    /**
     * Проверить активность
     * @return bool
     */
    public function isActive(): bool
    {
        return $this['ACTIVE'] == 'Y';
    }

    /**
     * Проверить идентификатор
     * @return int
     */
    public function getId(): int
    {
        return $this['ID'];
    }
}
