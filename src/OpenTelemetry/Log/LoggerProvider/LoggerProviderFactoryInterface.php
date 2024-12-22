<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LoggerProvider;

use OpenTelemetry\SDK\Logs\LoggerProviderInterface;
use OpenTelemetry\SDK\Logs\LogRecordProcessorInterface;
use OpenTelemetry\SDK\Resource\ResourceInfo;

interface LoggerProviderFactoryInterface
{
    public function createProvider(?LogRecordProcessorInterface $processor = null, ?ResourceInfo $resource = null): LoggerProviderInterface;
}
