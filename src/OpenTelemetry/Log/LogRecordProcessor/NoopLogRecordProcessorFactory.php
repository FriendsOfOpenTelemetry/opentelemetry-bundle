<?php

namespace GaelReyrol\OpenTelemetryBundle\OpenTelemetry\Log\LogRecordProcessor;

use OpenTelemetry\SDK\Logs\LogRecordExporterInterface;
use OpenTelemetry\SDK\Logs\LogRecordProcessorInterface;
use OpenTelemetry\SDK\Logs\Processor\NoopLogRecordProcessor;

final class NoopLogRecordProcessorFactory implements LogRecordProcessorFactoryInterface
{
    public static function create(
        array $processors = [],
        LogRecordExporterInterface $exporter = null,
    ): LogRecordProcessorInterface {
        return new NoopLogRecordProcessor();
    }
}
