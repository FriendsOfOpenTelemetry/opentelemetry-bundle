<?php

namespace GaelReyrol\OpenTelemetryBundle\OpenTelemetry\Log\LogRecordProcessor;

use OpenTelemetry\SDK\Logs\LogRecordExporterInterface;
use OpenTelemetry\SDK\Logs\LogRecordProcessorInterface;
use OpenTelemetry\SDK\Logs\Processor\MultiLogRecordProcessor;

final class MultiLogRecordProcessorFactory implements LogRecordProcessorFactoryInterface
{
    public static function create(
        array $processors = [],
        LogRecordExporterInterface $exporter = null,
    ): LogRecordProcessorInterface {
        if (0 === count($processors)) {
            throw new \InvalidArgumentException('Processors should not be empty');
        }

        return new MultiLogRecordProcessor($processors);
    }
}
