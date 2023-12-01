<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MetricExporter;

enum MetricTemporalityEnum: string
{
    case Delta = 'delta';
    case Cumulative = 'cumulative';

    case LowMemory = 'lowmemory';
}
