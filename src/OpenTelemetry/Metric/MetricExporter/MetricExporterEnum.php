<?php

namespace GaelReyrol\OpenTelemetryBundle\OpenTelemetry\Metric\MetricExporter;

enum MetricExporterEnum: string
{
    case Noop = 'noop';
    case Otlp = 'otlp';
    case Console = 'console';
    case InMemory = 'in_memory';
}
