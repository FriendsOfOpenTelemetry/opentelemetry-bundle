<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LogExporter;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterDsn;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterOptionsInterface;
use OpenTelemetry\SDK\Logs\LogRecordExporterInterface;

final class LogExporterFactory implements LogExporterFactoryInterface
{
    /**
     * @param iterable<mixed, LogExporterFactoryInterface> $factories
     */
    public function __construct(private readonly iterable $factories)
    {
    }

    public function supports(#[\SensitiveParameter] ExporterDsn $dsn, ExporterOptionsInterface $options): bool
    {
        foreach ($this->factories as $factory) {
            if ($factory->supports($dsn, $options)) {
                return true;
            }
        }

        return false;
    }

    public function createExporter(#[\SensitiveParameter] ExporterDsn $dsn, ExporterOptionsInterface $options): LogRecordExporterInterface
    {
        foreach ($this->factories as $factory) {
            if ($factory->supports($dsn, $options)) {
                return $factory->createExporter($dsn, $options);
            }
        }

        throw new \InvalidArgumentException('No Log exporter supports the given DSN.');
    }
}
