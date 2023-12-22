<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\SpanExporter;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterDsn;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterOptionsInterface;
use OpenTelemetry\SDK\Trace\SpanExporter\InMemoryExporter;

final readonly class InMemorySpanExporterFactory implements SpanExporterFactoryInterface
{
    public static function create(ExporterDsn $dsn, ExporterOptionsInterface $options): InMemoryExporter
    {
        return new InMemoryExporter();
    }
}
