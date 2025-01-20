<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LogProcessor;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LogProcessor\LogProcessorFactoryInterface;
use OpenTelemetry\SDK\Logs\LogRecordExporterInterface;
use OpenTelemetry\SDK\Logs\LogRecordProcessorInterface;
use OpenTelemetry\SDK\Logs\Processor\BatchLogRecordProcessor;

final class BatchLogProcessorFactory implements LogProcessorFactoryInterface
{
    public function createProcessor(array $processors = [], ?LogRecordExporterInterface $exporter = null): LogRecordProcessorInterface
    {
        return new BatchLogRecordProcessor();
    }
}
