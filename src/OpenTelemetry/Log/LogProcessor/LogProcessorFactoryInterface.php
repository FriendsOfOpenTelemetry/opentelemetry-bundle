<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LogProcessor;

use OpenTelemetry\API\Common\Time\ClockInterface;
use OpenTelemetry\API\Metrics\MeterProviderInterface;
use OpenTelemetry\SDK\Logs\LogRecordExporterInterface;
use OpenTelemetry\SDK\Logs\LogRecordProcessorInterface;

interface LogProcessorFactoryInterface
{
    /**
     * @param LogRecordProcessorInterface[] $processors
     * @param ?array{
     *      clock: ClockInterface,
     *      max_queue_size: int,
     *      schedule_delay: int,
     *      export_timeout: int,
     *      max_export_batch_size: int,
     *      auto_flush: bool,
     *      meter_provider: ?MeterProviderInterface,
     * } $batch
     */
    public function createProcessor(
        array $processors = [],
        ?array $batch = null,
        ?LogRecordExporterInterface $exporter = null,
    ): LogRecordProcessorInterface;
}
