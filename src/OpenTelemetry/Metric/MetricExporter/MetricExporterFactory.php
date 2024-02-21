<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MetricExporter;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterDsn;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterOptionsInterface;
use OpenTelemetry\SDK\Metrics\MetricExporterInterface;

final class MetricExporterFactory implements MetricExporterFactoryInterface
{
    /**
     * @param iterable<mixed, MetricExporterFactoryInterface> $factories
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

    public function createExporter(#[\SensitiveParameter] ExporterDsn $dsn, ExporterOptionsInterface $options): MetricExporterInterface
    {
        foreach ($this->factories as $factory) {
            if ($factory->supports($dsn, $options)) {
                return $factory->createExporter($dsn, $options);
            }
        }

        throw new \InvalidArgumentException('No Metric exporter factory supports the given DSN.');
    }
}
