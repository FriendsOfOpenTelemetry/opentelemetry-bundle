<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MetricExporter;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterDsn;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterOptionsInterface;
use OpenTelemetry\SDK\Metrics\MetricExporter\NoopMetricExporter;

final class NoopMetricExporterFactory implements MetricExporterFactoryInterface
{
    public static function createExporter(ExporterDsn $dsn = null, ExporterOptionsInterface $options = null): NoopMetricExporter
    {
        return new NoopMetricExporter();
    }
}
