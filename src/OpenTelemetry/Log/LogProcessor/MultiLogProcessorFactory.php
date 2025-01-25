<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LogProcessor;

use OpenTelemetry\SDK\Logs\LogRecordExporterInterface;
use OpenTelemetry\SDK\Logs\LogRecordProcessorInterface;
use OpenTelemetry\SDK\Logs\Processor\MultiLogRecordProcessor;

final class MultiLogProcessorFactory extends AbstractLogProcessorFactory
{
    public function createProcessor(
        array $processors = [],
        ?array $batch = null,
        ?LogRecordExporterInterface $exporter = null,
    ): LogRecordProcessorInterface {
        if (0 >= count($processors)) {
            throw new \InvalidArgumentException('You must provide at least one processor when using a multi log processor');
        }

        return new MultiLogRecordProcessor($processors);
    }
}
