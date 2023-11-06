<?php

namespace GaelReyrol\OpenTelemetryBundle\DependencyInjection;

enum OpenTelemetryProcessorEnum: string
{
    case Batch = 'batch';
    case Multi = 'multi';
    case Simple = 'simple';
    case Noop = 'noop';
}
