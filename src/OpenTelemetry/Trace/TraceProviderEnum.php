<?php

namespace GaelReyrol\OpenTelemetryBundle\OpenTelemetry\Trace;

enum TraceProviderEnum: string
{
    case Default = 'default';
    case Noop = 'noop';
    //    case Traceable = 'traceable';
}
