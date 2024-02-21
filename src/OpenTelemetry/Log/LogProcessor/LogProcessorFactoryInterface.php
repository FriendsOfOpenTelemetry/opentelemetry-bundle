<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LogProcessor;

use OpenTelemetry\SDK\Logs\LogRecordExporterInterface;
use OpenTelemetry\SDK\Logs\LogRecordProcessorInterface;

interface LogProcessorFactoryInterface
{
    /**
     * @param LogRecordProcessorInterface[] $processors
     */
    public function createProcessor(
        array $processors = [],
        ?LogRecordExporterInterface $exporter = null,
    ): LogRecordProcessorInterface;
}
