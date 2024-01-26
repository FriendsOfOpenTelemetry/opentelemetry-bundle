<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MetricExporter;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterDsn;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterOptionsInterface;
use OpenTelemetry\SDK\Metrics\MetricExporter\NoopMetricExporter;

final class NoopMetricExporterFactory extends AbstractMetricExporterFactory
{
    public function supports(#[\SensitiveParameter] ExporterDsn $dsn, ExporterOptionsInterface $options): bool
    {
        return MetricExporterEnum::Noop === MetricExporterEnum::tryFrom($dsn->getExporter());
    }

    public function createExporter(#[\SensitiveParameter] ExporterDsn $dsn, ExporterOptionsInterface $options): NoopMetricExporter
    {
        return new NoopMetricExporter();
    }
}
