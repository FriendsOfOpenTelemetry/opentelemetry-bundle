<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation;

class Debug
{
    /**
     * @return array{
     *     name: ?string,
     *     line: ?string,
     *     file: ?string,
     * }
     */
    public static function getCaller(): array
    {
        $trace = debug_backtrace(\DEBUG_BACKTRACE_IGNORE_ARGS, 8);

        $file = $trace[1]['file'] ?? null;
        $line = $trace[1]['line'] ?? null;

        $name = str_replace('\\', '/', (string) $file);

        return [
            'name' => substr($name, strrpos($name, '/') + 1),
            'file' => $file,
            'line' => $line,
        ];
    }
}
