<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LogProcessor;

use OpenTelemetry\SDK\Logs\LogRecordExporterInterface;
use OpenTelemetry\SDK\Logs\LogRecordProcessorInterface;
use OpenTelemetry\SDK\Logs\Processor\NoopLogRecordProcessor;

final class NoopLogProcessorFactory implements LogProcessorFactoryInterface
{
    public static function createProcessor(
        array $processors = [],
        LogRecordExporterInterface $exporter = null,
    ): LogRecordProcessorInterface {
        return new NoopLogRecordProcessor();
    }
}
