<?php

namespace Uru\BitrixModels\Models;

use ArrayIterator;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Str;
use Uru\BitrixModels\Models\Traits\HidesAttributes;

abstract class ArrayableModel implements \ArrayAccess, Arrayable, Jsonable, \IteratorAggregate
{
    use HidesAttributes;

    /**
     * ID of the model.
     */
    public ?int $id;

    /**
     * Array of model fields.
     */
    public mixed $fields;

    /**
     * Array related models indexed by the relation names.
     */
    public array $related = [];

    /**
     * Array of original model fields.
     */
    protected mixed $original = [];

    /**
     * Array of accessors to append during array transformation.
     */
    protected array $appends = [];

    /**
     * Array of language fields with auto accessors.
     */
    protected array $languageAccessors = [];

    /**
     * Set method for ArrayIterator.
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (is_null($offset)) {
            $this->fields[] = $value;
        } else {
            $this->fields[$offset] = $value;
        }
    }

    /**
     * Exists method for ArrayIterator.
     *
     * @param mixed $offset
     */
    public function offsetExists($offset): bool
    {
        return $this->getAccessor($offset) || $this->getAccessorForLanguageField(
            $offset
        ) || isset($this->fields[$offset]);
    }

    /**
     * Unset method for ArrayIterator.
     *
     * @param mixed $offset
     */
    public function offsetUnset($offset): void
    {
        unset($this->fields[$offset]);
    }

    /**
     * Get method for ArrayIterator.
     *
     * @param mixed $offset
     */
    public function offsetGet($offset): mixed
    {
        $fieldValue = $this->fields[$offset] ?? null;
        $accessor = $this->getAccessor($offset);
        if ($accessor) {
            return $this->{$accessor}($fieldValue);
        }

        $accessorForLanguageField = $this->getAccessorForLanguageField($offset);
        if ($accessorForLanguageField) {
            return $this->{$accessorForLanguageField}($offset);
        }

        return $fieldValue;
    }

    /**
     * Get an iterator for fields.
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->fields);
    }

    /**
     * Add value to append.
     *
     * @param array|string $attributes
     *
     * @return $this
     */
    public function append($attributes)
    {
        $this->appends = array_unique(
            array_merge($this->appends, is_string($attributes) ? func_get_args() : $attributes)
        );

        return $this;
    }

    /**
     * Setter for appends.
     *
     * @return $this
     */
    public function setAppends(array $appends)
    {
        $this->appends = $appends;

        return $this;
    }

    /**
     * Cast model to array.
     */
    public function toArray(): array
    {
        $array = $this->fields;

        foreach ($this->appends as $accessor) {
            if (isset($this[$accessor])) {
                $array[$accessor] = $this[$accessor];
            }
        }

        foreach ($this->related as $key => $value) {
            if (is_object($value) && method_exists($value, 'toArray')) {
                $array[$key] = $value->toArray();
            } elseif (is_null($value) || false === $value) {
                $array[$key] = $value;
            }
        }

        if (count($this->getVisible()) > 0) {
            $array = array_intersect_key($array, array_flip($this->getVisible()));
        }

        if (count($this->getHidden()) > 0) {
            $array = array_diff_key($array, array_flip($this->getHidden()));
        }

        return $array;
    }

    /**
     * Convert model to json.
     *
     * @param int $options
     */
    public function toJson($options = 0): string
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * Get accessor method name if it exists.
     *
     * @return false|string
     */
    private function getAccessor(string $field): bool|string
    {
        $method = 'get'.Str::camel($field).'Attribute';

        return method_exists($this, $method) ? $method : false;
    }

    /**
     * Get accessor for language field method name if it exists.
     *
     * @return false|string
     */
    private function getAccessorForLanguageField(string $field): bool|string
    {
        $method = 'getValueFromLanguageField';

        return in_array($field, $this->languageAccessors) && method_exists($this, $method) ? $method : false;
    }
}
