<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\SpanExporter;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterDsn;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterOptionsInterface;
use OpenTelemetry\SDK\Trace\SpanExporter\InMemoryExporter;

final readonly class InMemorySpanExporterFactory extends AbstractSpanExporterFactory
{
    public function supports(#[\SensitiveParameter] ExporterDsn $dsn, ExporterOptionsInterface $options): bool
    {
        return TraceExporterEnum::InMemory === TraceExporterEnum::tryFrom($dsn->getExporter());
    }

    public function createExporter(#[\SensitiveParameter] ExporterDsn $dsn, ExporterOptionsInterface $options): InMemoryExporter
    {
        return new InMemoryExporter();
    }
}
