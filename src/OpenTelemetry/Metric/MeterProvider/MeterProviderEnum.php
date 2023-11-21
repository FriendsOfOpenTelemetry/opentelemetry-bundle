<?php

namespace GaelReyrol\OpenTelemetryBundle\OpenTelemetry\Metric\MeterProvider;

enum MeterProviderEnum: string
{
    case Noop = 'noop';
    case Default = 'default';
}
