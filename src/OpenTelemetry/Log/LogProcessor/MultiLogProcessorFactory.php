<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LogProcessor;

use OpenTelemetry\SDK\Logs\LogRecordExporterInterface;
use OpenTelemetry\SDK\Logs\LogRecordProcessorInterface;
use OpenTelemetry\SDK\Logs\Processor\MultiLogRecordProcessor;

final class MultiLogProcessorFactory implements LogProcessorFactoryInterface
{
    public static function create(
        array $processors = null,
        LogRecordExporterInterface $exporter = null,
    ): LogRecordProcessorInterface {
        if (null === $processors) {
            throw new \InvalidArgumentException('Processors should not be empty');
        }

        return new MultiLogRecordProcessor($processors);
    }
}
