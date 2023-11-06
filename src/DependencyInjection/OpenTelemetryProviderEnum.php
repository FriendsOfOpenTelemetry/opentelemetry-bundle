<?php

namespace GaelReyrol\OpenTelemetryBundle\DependencyInjection;

enum OpenTelemetryProviderEnum: string
{
    case Default = 'default';
    case Noop = 'noop';
    case Traceable = 'traceable';
}
