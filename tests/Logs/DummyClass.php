<?php

namespace Uru\Tests\Logs;

use Uru\Logs\Logs;

class DummyClass
{
    use Logs;

    public function foo()
    {
        $this->logger()->error('error_here');
    }
}
