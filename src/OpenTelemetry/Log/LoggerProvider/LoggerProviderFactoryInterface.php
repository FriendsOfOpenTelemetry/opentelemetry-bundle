<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LoggerProvider;

use OpenTelemetry\SDK\Logs\LoggerProviderInterface;
use OpenTelemetry\SDK\Logs\LogRecordProcessorInterface;

interface LoggerProviderFactoryInterface
{
    public function createProvider(?LogRecordProcessorInterface $processor = null): LoggerProviderInterface;
}
