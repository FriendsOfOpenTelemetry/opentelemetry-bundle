<?php

namespace GaelReyrol\OpenTelemetryBundle\OpenTelemetry\Metric;

enum MetricTemporalityEnum: string
{
    case Delta = 'delta';
    case Cumulative = 'cumulative';

    case LowMemory = 'lowmemory';
}
