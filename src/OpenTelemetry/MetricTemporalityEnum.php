<?php

namespace GaelReyrol\OpenTelemetryBundle\OpenTelemetry;

enum MetricTemporalityEnum: string
{
    case Delta = 'delta';
    case Cumulative = 'cumulative';
}
