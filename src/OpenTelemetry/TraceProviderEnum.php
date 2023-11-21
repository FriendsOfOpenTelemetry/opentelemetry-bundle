<?php

namespace GaelReyrol\OpenTelemetryBundle\OpenTelemetry;

enum TraceProviderEnum: string
{
    case Default = 'default';
    case Noop = 'noop';
    //    case Traceable = 'traceable';
}
