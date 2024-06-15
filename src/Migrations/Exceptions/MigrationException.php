<?php

namespace Uru\BitrixMigrations\Exceptions;

/**
 * Class MigrationException.
 */
class MigrationException extends \Exception
{
    /**
     * @var int
     */
    protected $code = 1;
}
