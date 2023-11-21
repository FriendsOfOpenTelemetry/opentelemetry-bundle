<?php

namespace GaelReyrol\OpenTelemetryBundle\OpenTelemetry;

enum MetricProviderEnum: string
{
    case Noop = 'noop';
    case Default = 'default';
}
