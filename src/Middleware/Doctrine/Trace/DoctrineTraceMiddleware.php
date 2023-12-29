<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Middleware\Doctrine\Trace;

use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Driver\Middleware;

final class DoctrineTraceMiddleware implements Middleware
{
    public function wrap(Driver $driver): Driver
    {
        return new TraceConnectionDriver($driver);
    }
}
