<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\SpanProcessor;

enum SpanProcessorEnum: string
{
    //    case Batch = 'batch';
    case Multi = 'multi';
    case Simple = 'simple';
    case Noop = 'noop';
}
