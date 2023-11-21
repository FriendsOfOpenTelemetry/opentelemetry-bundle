<?php

namespace GaelReyrol\OpenTelemetryBundle\OpenTelemetry\Metric;

enum MetricExporterEnum: string
{
    case Noop = 'noop';
    case Default = 'default';
    case Console = 'console';
    case InMemory = 'in_memory';
}
