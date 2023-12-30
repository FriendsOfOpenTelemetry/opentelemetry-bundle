<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Middleware\Doctrine\Trace;

use Doctrine\DBAL\Driver as DriverInterface;
use Doctrine\DBAL\Driver\Middleware as MiddlewareInterface;
use OpenTelemetry\API\Trace\TracerInterface;

final class Middleware implements MiddlewareInterface
{
    public function __construct(private readonly TracerInterface $tracer)
    {
    }

    public function wrap(DriverInterface $driver): DriverInterface
    {
        return new Driver($driver, $this->tracer);
    }
}
