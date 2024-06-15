<?php

namespace Uru\Tests\Logs;

use Uru\Logs\EchoLogger;
use PHPUnit\Framework\TestCase;

class LogsTest extends TestCase
{
    public function test_working_without_a_logger()
    {
        $dummy = new DummyClass();
        $dummy->foo();

        $this->assertTrue(true);
    }

    public function test_working_with_psr_logger(): void
    {
        $dummy = new DummyClass();
        $dummy->setLogger(new EchoLogger());
        $dummy->foo();

        $this->expectOutputRegex('/\[(.*?)\] (.*?)/s');
    }

    public function test_working_with_echo_logger(): void
    {
        $dummy = new DummyClass();
        $dummy->setEchoLogger();
        $dummy->foo();

        $this->expectOutputRegex('/\[(.*?)\] (.*?)/s');
    }

    public function test_removing_a_logger()
    {
        $dummy = new DummyClass();
        $dummy->setEchoLogger();
        $dummy->removeLogger();
        $dummy->foo();

        $this->expectOutputString('');
    }
}
