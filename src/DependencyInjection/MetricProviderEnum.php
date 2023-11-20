<?php

namespace GaelReyrol\OpenTelemetryBundle\DependencyInjection;

enum MetricProviderEnum: string
{
    case Noop = 'noop';
    case Default = 'default';
}
