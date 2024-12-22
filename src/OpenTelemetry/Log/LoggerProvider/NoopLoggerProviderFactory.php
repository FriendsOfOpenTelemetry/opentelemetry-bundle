<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LoggerProvider;

use OpenTelemetry\SDK\Logs\LoggerProviderInterface;
use OpenTelemetry\SDK\Logs\LogRecordProcessorInterface;
use OpenTelemetry\SDK\Logs\NoopLoggerProvider;
use OpenTelemetry\SDK\Resource\ResourceInfo;

final class NoopLoggerProviderFactory extends AbstractLoggerProviderFactory
{
    public function createProvider(?LogRecordProcessorInterface $processor = null, ?ResourceInfo $resource = null): LoggerProviderInterface
    {
        return new NoopLoggerProvider();
    }
}
