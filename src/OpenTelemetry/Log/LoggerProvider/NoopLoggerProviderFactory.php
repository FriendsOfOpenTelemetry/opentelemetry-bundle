<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LoggerProvider;

use OpenTelemetry\SDK\Logs\LoggerProviderInterface;
use OpenTelemetry\SDK\Logs\LogRecordProcessorInterface;
use OpenTelemetry\SDK\Logs\NoopLoggerProvider;

final class NoopLoggerProviderFactory extends AbstractLoggerProviderFactory
{
    public function createProvider(LogRecordProcessorInterface $processor): LoggerProviderInterface
    {
        return new NoopLoggerProvider();
    }
}
