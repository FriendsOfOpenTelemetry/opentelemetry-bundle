<?php

namespace GaelReyrol\OpenTelemetryBundle\DependencyInjection;

enum SpanProcessorEnum: string
{
    case Noop = 'noop';
    case Simple = 'simple';
    case Multi = 'multi';
    //    case Batch = 'batch';
}
