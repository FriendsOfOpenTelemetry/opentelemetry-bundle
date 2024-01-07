<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Doctrine\Middleware;

use Doctrine\DBAL\Driver as DriverInterface;
use Doctrine\DBAL\Driver\Middleware as MiddlewareInterface;
use OpenTelemetry\API\Trace\TracerInterface;
use OpenTelemetry\Context\Context;

final class TraceableMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly TracerInterface $tracer)
    {
    }

    public function wrap(DriverInterface $driver): DriverInterface
    {
        $scope = Context::storage()->scope();
        if (null === $scope) {
            return $driver;
        }

        return new TraceableDriver($this->tracer, $driver);
    }
}
