<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\TracerProvider;

enum TraceSamplerEnum: string
{
    case AlwaysOn = 'always_on';
    case AlwaysOff = 'always_off';
    case TraceIdRatio = 'trace_id_ratio';
    case ParentBased = 'parent_based';
}
