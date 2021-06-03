<?php

namespace Uru\Tests\BitrixModels\Stubs;

use Uru\BitrixModels\Models\D7Model;

class TestD7Element extends D7Model
{
    public static function tableClass()
    {
        return TestD7ElementClass::class;
    }
}
