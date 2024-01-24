<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Doctrine\Middleware;

use Doctrine\DBAL\Driver as DriverInterface;
use Doctrine\DBAL\Driver\Middleware as MiddlewareInterface;
use OpenTelemetry\API\Trace\TracerInterface;
use OpenTelemetry\Context\Context;
use Psr\Log\LoggerInterface;

final class TraceableMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly TracerInterface $tracer,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function wrap(DriverInterface $driver): DriverInterface
    {
        $scope = Context::storage()->scope();
        if (null === $scope) {
            $this->logger->debug('No scope is available to register new spans.');

            return $driver;
        }

        return new TraceableDriver($this->tracer, $driver);
    }
}
