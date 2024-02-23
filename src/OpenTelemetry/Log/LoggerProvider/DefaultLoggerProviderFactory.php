<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LoggerProvider;

use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeFactory;
use OpenTelemetry\SDK\Logs\LoggerProvider;
use OpenTelemetry\SDK\Logs\LoggerProviderInterface;
use OpenTelemetry\SDK\Logs\LogRecordLimitsBuilder;
use OpenTelemetry\SDK\Logs\LogRecordProcessorInterface;

final class DefaultLoggerProviderFactory extends AbstractLoggerProviderFactory
{
    public function createProvider(?LogRecordProcessorInterface $processor = null): LoggerProviderInterface
    {
        $instrumentationScopeFactory = new InstrumentationScopeFactory((new LogRecordLimitsBuilder())->build()->getAttributeFactory());

        assert($processor instanceof LogRecordProcessorInterface);

        return new LoggerProvider($processor, $instrumentationScopeFactory);
    }
}
