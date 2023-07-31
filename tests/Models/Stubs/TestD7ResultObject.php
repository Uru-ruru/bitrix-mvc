<?php

namespace Uru\Tests\BitrixModels\Stubs;


use Uru\BitrixModels\Models\Interfaces\ResultInterface;

class TestD7ResultObject implements ResultInterface
{
    public function isSuccess($internalCall = false): bool
    {
        return true;
    }
    
    public function getId(): int
    {
        return 1;
    }
}
