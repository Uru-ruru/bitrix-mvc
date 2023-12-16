<?php

use Uru\BitrixModels\Models\ElementModel;
use Uru\BitrixModels\Queries\ElementQuery;

/**
 * Class BaseElementModel.
 *
 * @method static ElementQuery fromSectionTreeById(int $id)
 */
class BaseElementModel extends ElementModel
{
    /**
     * @var bool|string
     */
    public static $iblockCode = false;

    /**
     * @throws Exception
     */
    public static function iblockId(): int
    {
        if (!static::$iblockCode) {
            throw new Exception('Необходимо определить public static $iblockCode');
        }

        return Uru\BitrixIblockHelper\IblockId::getByCode(static::$iblockCode);
    }

    public static function baseQuery(): ElementQuery
    {
        return static::query()->sort('SORT')->filter(['ACTIVE' => 'Y']);
    }

    /**
     * Проверить идентификатор
     */
    public function getId(): int
    {
        return $this['ID'];
    }

    /**
     * Получить название.
     */
    public function getName(): string
    {
        return $this['NAME'];
    }

    /**
     * Проверить активность.
     */
    public function isActive(): bool
    {
        return 'Y' == $this['ACTIVE'];
    }

    /**
     * Получить краткое описание.
     */
    public function getPreviewText(): string
    {
        return $this['PREVIEW_TEXT'];
    }

    /**
     * Получить полное описание.
     */
    public function getDetailText(): string
    {
        return $this['DETAIL_TEXT'];
    }

    /**
     * Scope to get only items from a given section.
     *
     * @param mixed $id
     */
    public function scopeFromSectionTreeById(ElementQuery $query, $id): ElementQuery
    {
        $query->filter['SECTION_ID'] = $id;
        $query->filter['INCLUDE_SUBSECTIONS'] = 'Y';

        return $query;
    }

    public function getCreatedAt(): DateTime
    {
        return DateTime::createFromFormat('U', (int) $this['DATE_CREATE_UNIX']);
    }

    public function getCreatedAtFormated(string $format = 'd.m.Y, H:i'): string
    {
        return $this->getCreatedAt()->format($format);
    }
}
