<?php

namespace Uru\BitrixModels\Adapters;

use Bitrix\Main\DB\Result;
use Bitrix\Main\Entity\AddResult;
use Bitrix\Main\Entity\DeleteResult;
use Bitrix\Main\Entity\UpdateResult;

/**
 * Class D7Adapter
 *
 * @method Result getList(array $parameters = [])
 * @method int getCount(array $filter = [])
 * @method UpdateResult update(int $id, array $fields)
 * @method DeleteResult delete(int $id)
 * @method AddResult add(array $fields)
 */
class D7Adapter
{
    /**
     * Bitrix Class FQCN.
     *
     * @var string
     */
    protected string $className;

    /**
     * D7Adapter constructor.
     *
     * @param $className
     */
    public function __construct($className)
    {
        $this->className = $className;
    }

    /**
     * Getter for class name.
     *
     * @return string
     */
    public function getClassName(): string
    {
        return $this->className;
    }

    /**
     * Handle dynamic method calls into a static calls on bitrix entity class.
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public function __call(string $method, array $parameters)
    {
        $className = $this->className;

        return $className::$method(...$parameters);
    }
}
