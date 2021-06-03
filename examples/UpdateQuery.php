<?php

/**
 * Class UpdateQuery
 * @package App\Models
 */
class UpdateQuery
{
    /**
     * @var array
     */
    private array $query = [];

    /**
     * @var array|null
     */
    private $fields;
    /**
     * @var object|null
     */
    private ?object $object;

    /**
     * UpdateQuery constructor.
     * @param object|null $element
     */
    public function __construct($element = null)
    {
        $this->object = $element;
        $this->fields = property_exists($element, 'fields') ? $element->fields : null;
    }

    /**
     * @param $paramName
     * @param $paramValue
     * @return $this
     * @throws Exception
     */
    public function setParam($paramName, $paramValue): UpdateQuery
    {
        if ($this->fields && !array_key_exists($paramName, $this->fields)) {
            throw new Exception('A field with this "' . $paramName . '" was not found in this object.');
        }
        $this->query[$paramName] = $paramValue;
        return $this;
    }

    /**
     * @param $paramName
     * @return string|null
     */
    public function getParam($paramName): ?string
    {
        if (array_key_exists($paramName, $this->query)) {
            return $this->query[$paramName];
        }
    }

    /**
     * @return array
     */
    public function getQuery(): array
    {
        return $this->query;
    }

    /**
     * @return void
     */
    public function update(): void
    {
        if ($this->object && method_exists($this->object, 'update')) {
            $this->object->update($this->query);
        }
    }

}
