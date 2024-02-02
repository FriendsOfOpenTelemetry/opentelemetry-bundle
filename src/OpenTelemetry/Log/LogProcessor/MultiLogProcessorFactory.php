<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LogProcessor;

use OpenTelemetry\SDK\Logs\LogRecordExporterInterface;
use OpenTelemetry\SDK\Logs\LogRecordProcessorInterface;
use OpenTelemetry\SDK\Logs\Processor\MultiLogRecordProcessor;

final class MultiLogProcessorFactory extends AbstractLogProcessorFactory
{
    public static function createProcessor(
        array $processors = [],
        ?LogRecordExporterInterface $exporter = null,
    ): LogRecordProcessorInterface {
        if (0 >= count($processors)) {
            throw new \InvalidArgumentException('Processors should not be empty');
        }

        return new MultiLogRecordProcessor($processors);
    }
}
