<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LoggerProvider;

use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeFactory;
use OpenTelemetry\SDK\Logs\LoggerProvider;
use OpenTelemetry\SDK\Logs\LoggerProviderInterface;
use OpenTelemetry\SDK\Logs\LogRecordLimitsBuilder;
use OpenTelemetry\SDK\Logs\LogRecordProcessorInterface;
use OpenTelemetry\SDK\Resource\ResourceInfo;

final class DefaultLoggerProviderFactory extends AbstractLoggerProviderFactory
{
    public function createProvider(?LogRecordProcessorInterface $processor = null, ?ResourceInfo $resource = null): LoggerProviderInterface
    {
        $instrumentationScopeFactory = new InstrumentationScopeFactory((new LogRecordLimitsBuilder())->build()->getAttributeFactory());

        assert($processor instanceof LogRecordProcessorInterface);

        return new LoggerProvider($processor, $instrumentationScopeFactory, $resource);
    }
}
