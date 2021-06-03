<?php

namespace Uru\Tests\Logs;

use Uru\Logs\EchoLogger;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;

class EchoLoggerTest extends TestCase
{
    public function test_it_can_echo_errors()
    {
        $logger = new EchoLogger();
        $logger->log(LogLevel::ERROR, 'error_message_here');

        $this->expectOutputRegex('/\[(.*?)\] error: error_message_here/s');
    }
}
