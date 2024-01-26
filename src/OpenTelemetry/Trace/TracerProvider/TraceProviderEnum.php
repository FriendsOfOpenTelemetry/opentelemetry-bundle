<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\TracerProvider;

enum TraceProviderEnum: string
{
    case Default = 'default';
    case Noop = 'noop';
    //    case Traceable = 'traceable';
}
