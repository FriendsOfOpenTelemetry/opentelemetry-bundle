<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LogProcessor;

use OpenTelemetry\SDK\Logs\LogRecordExporterInterface;
use OpenTelemetry\SDK\Logs\LogRecordProcessorInterface;
use OpenTelemetry\SDK\Logs\Processor\SimpleLogRecordProcessor;

final class SimpleLogProcessorFactory implements LogProcessorFactoryInterface
{
    public static function createProcessor(
        array $processors = [],
        LogRecordExporterInterface $exporter = null,
    ): LogRecordProcessorInterface {
        if (null === $exporter) {
            throw new \InvalidArgumentException('Exporter is null');
        }

        return new SimpleLogRecordProcessor($exporter);
    }
}
