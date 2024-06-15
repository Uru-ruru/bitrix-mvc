<?php

namespace Uru\BitrixModels;

use Illuminate\Support\Collection;
use Uru\BitrixModels\Models\BaseBitrixModel;

class Helpers
{
    /**
     * Does the $haystack starts with $needle.
     *
     * @param mixed $haystack
     * @param mixed $needle
     */
    public static function startsWith($haystack, $needle): bool
    {
        return 0 === strncmp($haystack, $needle, strlen($needle));
    }

    /**
     * @param BaseBitrixModel[]|Collection $primaryModels  первичные модели
     * @param BaseBitrixModel[]|Collection $relationModels подгруженные связанные модели
     * @param string                       $primaryKey     ключ связи в первичной модели
     * @param string                       $relationKey    ключ связи в связанной модели
     * @param string                       $relationName   название связи в первичной модели
     * @param bool                         $multiple       множественная ли это свзязь
     */
    public static function assocModels($primaryModels, $relationModels, $primaryKey, $relationKey, $relationName, $multiple)
    {
        $buckets = static::buildBuckets($relationModels, $relationKey, $multiple);

        foreach ($primaryModels as $i => $primaryModel) {
            if ($multiple && is_array($keys = $primaryModel[$primaryKey])) {
                $value = [];
                foreach ($keys as $key) {
                    $key = static::normalizeModelKey($key);
                    if (isset($buckets[$key])) {
                        $value = array_merge($value, $buckets[$key]);
                    }
                }
            } else {
                $key = static::normalizeModelKey($primaryModel[$primaryKey]);
                $value = $buckets[$key] ?? ($multiple ? [] : null);
            }

            $primaryModel->populateRelation($relationName, is_array($value) ? (new Collection($value))->keyBy(function ($item) {return $item->id; }) : $value);
        }
    }

    /**
     * Сгруппировать найденные модели.
     *
     * @param array $models
     */
    protected static function buildBuckets($models, string $linkKey, bool $multiple): array
    {
        $buckets = [];

        foreach ($models as $model) {
            $key = $model[$linkKey];
            if (is_scalar($key)) {
                $buckets[$key][] = $model;
            } elseif (is_array($key)) {
                foreach ($key as $k) {
                    $k = static::normalizeModelKey($k);
                    $buckets[$k][] = $model;
                }
            } else {
                $key = static::normalizeModelKey($key);
                $buckets[$key][] = $model;
            }
        }

        if (!$multiple) {
            foreach ($buckets as $i => $bucket) {
                $buckets[$i] = reset($bucket);
            }
        }

        return $buckets;
    }

    /**
     * @param mixed $value raw key value
     *
     * @return string normalized key value
     */
    protected static function normalizeModelKey($value): ?string
    {
        if (is_object($value) && method_exists($value, '__toString')) {
            // ensure matching to special objects, which are convertable to string, for cross-DBMS relations, for example: `|MongoId`
            $value = $value->__toString();
        }

        return $value;
    }
}
