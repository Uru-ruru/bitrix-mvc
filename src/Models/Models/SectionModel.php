<?php

namespace Uru\BitrixModels\Models;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Uru\BitrixModels\Exceptions\ExceptionFromBitrix;
use Uru\BitrixModels\Queries\SectionQuery;

/**
 * SectionQuery methods.
 *
 * @method static static       getByCode(string $code)
 * @method static static       getByExternalId(string $id)
 * @method static SectionQuery countElements($value)
 *
 * Base Query methods
 * @method static Collection|static[]  getList()
 * @method static static               first()
 * @method static static               getById(int $id)
 * @method static SectionQuery         sort(string|array $by, string $order = 'ASC')
 * @method static SectionQuery         order(string|array $by, string $order = 'ASC') // same as sort()
 * @method static SectionQuery         filter(array $filter)
 * @method static SectionQuery         addFilter(array $filters)
 * @method static SectionQuery         resetFilter()
 * @method static SectionQuery         navigation(array $filter)
 * @method static SectionQuery         select($value)
 * @method static SectionQuery         keyBy(string $value)
 * @method static SectionQuery         limit(int $value)
 * @method static SectionQuery         offset(int $value)
 * @method static SectionQuery         page(int $num)
 * @method static SectionQuery         take(int $value) // same as limit()
 * @method static SectionQuery         forPage(int $page, int $perPage = 15)
 * @method static LengthAwarePaginator paginate(int $perPage = 15, string $pageName = 'page')
 * @method static Paginator            simplePaginate(int $perPage = 15, string $pageName = 'page')
 * @method static SectionQuery         stopQuery()
 * @method static SectionQuery         cache(float|int $minutes)
 *
 * Scopes
 * @method static SectionQuery active()
 * @method static SectionQuery childrenOf(SectionModel $section)
 * @method static SectionQuery directChildrenOf(SectionModel|int $section)
 */
class SectionModel extends BitrixModel
{
    /**
     * Corresponding IBLOCK_ID.
     *
     * @var int
     */
    public const IBLOCK_ID = null;

    /**
     * Bitrix entity object.
     *
     * @var object
     */
    public static $bxObject;

    /**
     * Corresponding object class name.
     */
    protected static string $objectClass = 'CIBlockSection';

    /**
     * Recalculate LEFT_MARGIN and RIGHT_MARGIN during add/update ($bResort for CIBlockSection::Add/Update).
     */
    protected static bool $resort = true;

    /**
     * Update search after each create or update.
     */
    protected static bool $updateSearch = true;

    /**
     * Resize pictures during add/update ($bResizePictures for CIBlockSection::Add/Update).
     */
    protected static bool $resizePictures = false;

    /**
     * Getter for corresponding iblock id.
     *
     * @throws \LogicException
     */
    public static function iblockId(): int
    {
        $id = static::IBLOCK_ID;
        if (!$id) {
            throw new \LogicException('You must set $iblockId property or override iblockId() method');
        }

        return $id;
    }

    /**
     * Instantiate a query object for the model.
     */
    public static function query(): SectionQuery
    {
        return new SectionQuery(static::instantiateObject(), get_called_class());
    }

    /**
     * Create new item in database.
     *
     * @param mixed $fields
     *
     * @return bool|static
     *
     * @throws ExceptionFromBitrix
     */
    public static function create($fields)
    {
        if (!isset($fields['IBLOCK_ID'])) {
            $fields['IBLOCK_ID'] = static::iblockId();
        }

        return static::internalCreate($fields);
    }

    /**
     * Get IDs of direct children of the section.
     * Additional filter can be specified.
     */
    public function getDirectChildren(array $filter = []): array
    {
        return static::query()
            ->filter($filter)
            ->filter(['SECTION_ID' => $this->id])
            ->select('ID')
            ->getList()
            ->transform(function ($section) {
                return (int) $section['ID'];
            })
            ->all()
        ;
    }

    /**
     * Get IDs of all children of the section (direct or not).
     * Additional filter can be specified.
     *
     * @param array|string $sort
     */
    public function getAllChildren(array $filter = [], $sort = ['LEFT_MARGIN' => 'ASC']): array
    {
        if (!isset($this->fields['LEFT_MARGIN']) || !isset($this->fields['RIGHT_MARGIN'])) {
            $this->refresh();
        }

        return static::query()
            ->sort($sort)
            ->filter($filter)
            ->filter([
                '!ID' => $this->id,
                '>LEFT_MARGIN' => $this->fields['LEFT_MARGIN'],
                '<RIGHT_MARGIN' => $this->fields['RIGHT_MARGIN'],
            ])
            ->select('ID')
            ->getList()
            ->transform(function ($section) {
                return (int) $section['ID'];
            })
            ->all()
        ;
    }

    /**
     * Proxy for GetPanelButtons.
     */
    public function getPanelButtons(array $options = []): array
    {
        return \CIBlock::GetPanelButtons(
            static::iblockId(),
            0,
            $this->id,
            $options
        );
    }

    public static function internalDirectCreate($bxObject, $fields)
    {
        return $bxObject->add($fields, static::$resort, static::$updateSearch, static::$resizePictures);
    }

    public static function setResort($value)
    {
        static::$resort = $value;
    }

    public static function setUpdateSearch($value)
    {
        static::$updateSearch = $value;
    }

    public static function setResizePictures($value)
    {
        static::$resizePictures = $value;
    }

    public function scopeChildrenOf(SectionQuery $query, SectionModel $section): SectionQuery
    {
        $query->filter['>LEFT_MARGIN'] = $section->fields['LEFT_MARGIN'];
        $query->filter['<RIGHT_MARGIN'] = $section->fields['RIGHT_MARGIN'];
        $query->filter['>DEPTH_LEVEL'] = $section->fields['DEPTH_LEVEL'];

        return $query;
    }

    /**
     * @param int|SectionModel $section
     */
    public function scopeDirectChildrenOf(SectionQuery $query, $section): SectionQuery
    {
        $query->filter['SECTION_ID'] = is_int($section) ? $section : $section->id;

        return $query;
    }

    /**
     * @param mixed $fieldsSelectedForSave
     * @param mixed $fields
     */
    protected function internalUpdate($fields, $fieldsSelectedForSave): bool
    {
        return !empty($fields) ? static::$bxObject->update($this->id, $fields, static::$resort, static::$updateSearch, static::$resizePictures) : false;
    }
}
