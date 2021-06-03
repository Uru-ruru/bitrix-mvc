<?php

namespace Uru\BitrixMigrations\Exceptions;

use Exception;

/**
 * Class MigrationException
 * @package Uru\BitrixMigrations\Exceptions
 */
class MigrationException extends Exception
{
    /**
     * @var int
     */
    protected $code = 1;
}
