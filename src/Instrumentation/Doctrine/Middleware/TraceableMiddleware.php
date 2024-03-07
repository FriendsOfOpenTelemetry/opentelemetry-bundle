<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Doctrine\Middleware;

use Doctrine\DBAL\Driver as DriverInterface;
use Doctrine\DBAL\Driver\Middleware as MiddlewareInterface;
use OpenTelemetry\API\Trace\TracerInterface;
use Psr\Log\LoggerInterface;

final class TraceableMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly TracerInterface $tracer,
        private readonly ?LoggerInterface $logger = null,
    ) {
    }

    public function wrap(DriverInterface $driver): DriverInterface
    {
        return new TraceableDriver($this->tracer, $driver, $this->logger);
    }
}
