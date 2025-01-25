<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LogProcessor;

use OpenTelemetry\SDK\Logs\LogRecordExporterInterface;
use OpenTelemetry\SDK\Logs\LogRecordProcessorInterface;
use OpenTelemetry\SDK\Logs\Processor\BatchLogRecordProcessor;

final class BatchLogProcessorFactory extends AbstractLogProcessorFactory
{
    public function createProcessor(
        array $processors = [],
        ?array $batch = null,
        ?LogRecordExporterInterface $exporter = null,
    ): LogRecordProcessorInterface {
        if (false === $exporter instanceof LogRecordExporterInterface) {
            throw new \InvalidArgumentException('You must provide an exporter when using a batch log processor');
        }

        if (null === $batch) {
            throw new \InvalidArgumentException('You must provide a batch configuration when using a batch log processor');
        }

        return new BatchLogRecordProcessor(
            $exporter,
            $batch['clock'],
            $batch['max_queue_size'],
            $batch['schedule_delay'],
            $batch['export_timeout'],
            $batch['max_export_batch_size'],
            $batch['auto_flush'],
            $batch['meter_provider'],
        );
    }
}
