<?php

namespace GaelReyrol\OpenTelemetryBundle\Factory;

use OpenTelemetry\SDK\Trace\SpanExporter\InMemoryExporter;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;

final class InMemorySpanExporterFactory implements SpanExporterFactoryInterface
{
    public function createFromOptions(array $options): SpanExporterInterface
    {
        return new InMemoryExporter();
    }
}
