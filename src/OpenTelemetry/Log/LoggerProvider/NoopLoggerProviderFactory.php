<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LoggerProvider;

use OpenTelemetry\API\Logs\LoggerProviderInterface;
use OpenTelemetry\SDK\Logs\LogRecordProcessorInterface;
use OpenTelemetry\SDK\Logs\NoopLoggerProvider;

final class NoopLoggerProviderFactory implements LoggerProviderFactoryInterface
{
    public static function create(LogRecordProcessorInterface $processor): LoggerProviderInterface
    {
        return new NoopLoggerProvider();
    }
}
