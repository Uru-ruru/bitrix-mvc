<?php

use Uru\BitrixModels\Models\ElementModel;
use Uru\BitrixModels\Queries\ElementQuery;

/**
 * Class BaseElementModel
 *
 * @method static ElementQuery fromSectionTreeById(int $id)
 */
class BaseElementModel extends ElementModel
{
    /**
     * @var string|bool
     */
    public static $iblockCode = false;

    /**
     * @return int
     * @throws Exception
     */
    public static function iblockId(): int
    {
        if (!static::$iblockCode) {
            throw new Exception("Необходимо определить public static \$iblockCode");
        }

        return Uru\BitrixIblockHelper\IblockId::getByCode(static::$iblockCode);
    }

    /**
     * @return ElementQuery
     */
    public static function baseQuery(): ElementQuery
    {
        return static::query()->sort('SORT')->filter(['ACTIVE' => 'Y']);
    }

    /**
     * Проверить идентификатор
     * @return int
     */
    public function getId(): int
    {
        return $this['ID'];
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
     * Проверить активность
     * @return bool
     */
    public function isActive(): bool
    {
        return $this['ACTIVE'] == 'Y';
    }

    /**
     * Получить краткое описание
     * @return string
     */
    public function getPreviewText(): string
    {
        return $this['PREVIEW_TEXT'];
    }

    /**
     * Получить полное описание
     * @return string
     */
    public function getDetailText(): string
    {
        return $this['DETAIL_TEXT'];
    }

    /**
     * Scope to get only items from a given section.
     *
     * @param ElementQuery $query
     * @param mixed $id
     *
     * @return ElementQuery
     */
    public function scopeFromSectionTreeById(ElementQuery $query, $id): ElementQuery
    {
        $query->filter['SECTION_ID'] = $id;
        $query->filter['INCLUDE_SUBSECTIONS'] = "Y";

        return $query;
    }

    /**
     * @return DateTime
     */
    public function getCreatedAt(): DateTime
    {
        return DateTime::createFromFormat('U', (int)$this['DATE_CREATE_UNIX']);
    }

    /**
     * @param string $format
     *
     * @return string
     */
    public function getCreatedAtFormated(string $format = 'd.m.Y, H:i'): string
    {
        return $this->getCreatedAt()->format($format);
    }


}
