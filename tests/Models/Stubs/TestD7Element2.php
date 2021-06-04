<?php

namespace Uru\Tests\BitrixModels\Stubs;

use Uru\BitrixModels\Models\D7Model;

class TestD7Element2 extends D7Model
{
    public static function tableClass(): string
    {
        return TestD7ElementClass2::class;
    }
}
