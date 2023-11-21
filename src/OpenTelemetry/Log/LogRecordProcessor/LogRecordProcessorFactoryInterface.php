<?php

namespace GaelReyrol\OpenTelemetryBundle\OpenTelemetry\Log\LogRecordProcessor;

use OpenTelemetry\SDK\Logs\LogRecordExporterInterface;
use OpenTelemetry\SDK\Logs\LogRecordProcessorInterface;

interface LogRecordProcessorFactoryInterface
{
    /**
     * @param LogRecordProcessorInterface[] $processors
     */
    public static function create(
        array $processors = [],
        LogRecordExporterInterface $exporter = null,
    ): LogRecordProcessorInterface;
}
