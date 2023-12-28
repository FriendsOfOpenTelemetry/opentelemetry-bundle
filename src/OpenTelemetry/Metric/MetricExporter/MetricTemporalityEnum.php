<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MetricExporter;

use OpenTelemetry\SDK\Metrics\Data\Temporality;

enum MetricTemporalityEnum: string
{
    case Delta = 'delta';
    case Cumulative = 'cumulative';

    case LowMemory = 'low_memory';

    public function toData(): string
    {
        return match ($this) {
            self::Delta => Temporality::DELTA,
            self::Cumulative => Temporality::CUMULATIVE,
            self::LowMemory => 'LowMemory',
        };
    }
}
