<?php

namespace GaelReyrol\OpenTelemetryBundle\DependencyInjection;

enum SpanProcessorEnum: string
{
    case Batch = 'batch';
    case Multi = 'multi';
    case Simple = 'simple';
    case Noop = 'noop';
}
