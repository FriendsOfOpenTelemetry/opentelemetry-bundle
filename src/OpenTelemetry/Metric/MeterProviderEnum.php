<?php

namespace GaelReyrol\OpenTelemetryBundle\OpenTelemetry\Metric;

enum MeterProviderEnum: string
{
    case Noop = 'noop';
    case Default = 'default';
}
