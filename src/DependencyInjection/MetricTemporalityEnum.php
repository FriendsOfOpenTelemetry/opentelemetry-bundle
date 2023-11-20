<?php

namespace GaelReyrol\OpenTelemetryBundle\DependencyInjection;

enum MetricTemporalityEnum: string
{
    case Delta = 'delta';
    case Cumulative = 'cumulative';
}
