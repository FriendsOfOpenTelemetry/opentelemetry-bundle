<?php

namespace GaelReyrol\OpenTelemetryBundle\DependencyInjection;

enum TraceProviderEnum: string
{
    case Default = 'default';
    case Noop = 'noop';
    case Traceable = 'traceable';
}
