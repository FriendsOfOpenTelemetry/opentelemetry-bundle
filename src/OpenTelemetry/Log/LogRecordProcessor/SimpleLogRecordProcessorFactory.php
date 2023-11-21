<?php

namespace GaelReyrol\OpenTelemetryBundle\OpenTelemetry\Log\LogRecordProcessor;

use OpenTelemetry\SDK\Logs\LogRecordExporterInterface;
use OpenTelemetry\SDK\Logs\LogRecordProcessorInterface;
use OpenTelemetry\SDK\Logs\Processor\SimpleLogRecordProcessor;

final class SimpleLogRecordProcessorFactory implements LogRecordProcessorFactoryInterface
{
    public static function create(
        array $processors = [],
        LogRecordExporterInterface $exporter = null,
    ): LogRecordProcessorInterface {
        if (null === $exporter) {
            throw new \RuntimeException('Exporter is null');
        }

        return new SimpleLogRecordProcessor($exporter);
    }
}
