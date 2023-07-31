<?php

namespace Uru\Logs;

use Psr\Log\AbstractLogger;

class EchoLogger extends AbstractLogger
{
    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public function log($level, $message, array $context = array()): void
    {
        $dateTime = date('Y-m-d H:i:s');
        $newLine = PHP_SAPI === 'cli' ? "\r\n" : "<br>";

        echo "[{$dateTime}] {$level}: {$message}{$newLine}";
    }
}
