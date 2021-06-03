<?php

namespace Uru\Tests\BitrixModels;

use Mockery;
use PHPUnit\Framework\TestCase;

abstract class ModelTestCase extends TestCase
{
    public function tearDown(): void
    {
        Mockery::close();
    }
}
