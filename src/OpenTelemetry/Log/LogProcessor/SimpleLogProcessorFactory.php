<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LogProcessor;

use OpenTelemetry\SDK\Logs\LogRecordExporterInterface;
use OpenTelemetry\SDK\Logs\LogRecordProcessorInterface;
use OpenTelemetry\SDK\Logs\Processor\SimpleLogRecordProcessor;

final class SimpleLogProcessorFactory extends AbstractLogProcessorFactory
{
    public function createProcessor(
        array $processors = [],
        ?array $batch = null,
        ?LogRecordExporterInterface $exporter = null,
    ): LogRecordProcessorInterface {
        if (false === $exporter instanceof LogRecordExporterInterface) {
            throw new \InvalidArgumentException('You must provide an exporter when using a simple log processor');
        }

        return new SimpleLogRecordProcessor($exporter);
    }
}
