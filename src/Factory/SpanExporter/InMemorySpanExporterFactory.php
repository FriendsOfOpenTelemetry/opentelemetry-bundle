<?php

namespace GaelReyrol\OpenTelemetryBundle\Factory\SpanExporter;

use OpenTelemetry\SDK\Trace\SpanExporter\InMemoryExporter;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;

final readonly class InMemorySpanExporterFactory implements SpanExporterFactoryInterface
{
    public static function createFromOptions(array $options): SpanExporterInterface
    {
        return new InMemoryExporter();
    }
}
