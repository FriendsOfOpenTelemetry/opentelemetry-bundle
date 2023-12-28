<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MetricExporter;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterDsn;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterOptionsInterface;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MetricExporterOptions;
use OpenTelemetry\SDK\Metrics\MetricExporter\InMemoryExporter;

final class InMemoryMetricExporterFactory implements MetricExporterFactoryInterface
{
    public static function createExporter(ExporterDsn $dsn, ExporterOptionsInterface $options): InMemoryExporter
    {
        assert($options instanceof MetricExporterOptions);

        return new InMemoryExporter($options->getTemporality()->toData());
    }
}
