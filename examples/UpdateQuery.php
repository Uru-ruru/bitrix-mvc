<?php

/**
 * Class UpdateQuery.
 */
class UpdateQuery
{
    private array $query = [];

    /**
     * @var null|array
     */
    private $fields;

    private ?object $object;

    /**
     * UpdateQuery constructor.
     *
     * @param null|object $element
     */
    public function __construct($element = null)
    {
        $this->object = $element;
        $this->fields = property_exists($element, 'fields') ? $element->fields : null;
    }

    /**
     * @param mixed $paramName
     * @param mixed $paramValue
     *
     * @return $this
     *
     * @throws Exception
     */
    public function setParam($paramName, $paramValue): UpdateQuery
    {
        if ($this->fields && !array_key_exists($paramName, $this->fields)) {
            throw new Exception('A field with this "'.$paramName.'" was not found in this object.');
        }
        $this->query[$paramName] = $paramValue;

        return $this;
    }

    public function getParam($paramName): ?string
    {
        if (array_key_exists($paramName, $this->query)) {
            return $this->query[$paramName];
        }
    }

    public function getQuery(): array
    {
        return $this->query;
    }

    public function update(): void
    {
        if ($this->object && method_exists($this->object, 'update')) {
            $this->object->update($this->query);
        }
    }
}
