<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MetricExporter;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterDsn;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterOptionsInterface;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MetricExporterOptions;
use OpenTelemetry\SDK\Metrics\MetricExporter\InMemoryExporter;

final class InMemoryMetricExporterFactory extends AbstractMetricExporterFactory
{
    public function supports(#[\SensitiveParameter] ExporterDsn $dsn, ExporterOptionsInterface $options): bool
    {
        if (!$options instanceof MetricExporterOptions) {
            return false;
        }

        return MetricExporterEnum::InMemory === MetricExporterEnum::tryFrom($dsn->getExporter());
    }

    public function createExporter(#[\SensitiveParameter] ExporterDsn $dsn, ExporterOptionsInterface $options): InMemoryExporter
    {
        assert($options instanceof MetricExporterOptions);

        return new InMemoryExporter(temporality: $options->getTemporality()->toData());
    }
}
