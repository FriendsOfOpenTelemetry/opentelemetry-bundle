<?php

namespace GaelReyrol\OpenTelemetryBundle\OpenTelemetry\Log\LogProcessor;

use OpenTelemetry\SDK\Logs\LogRecordExporterInterface;
use OpenTelemetry\SDK\Logs\LogRecordProcessorInterface;

interface LogProcessorFactoryInterface
{
    /**
     * @param LogRecordProcessorInterface[] $processors
     */
    public static function create(
        array $processors = null,
        LogRecordExporterInterface $exporter = null,
    ): LogRecordProcessorInterface;
}
