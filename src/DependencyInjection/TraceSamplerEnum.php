<?php

namespace GaelReyrol\OpenTelemetryBundle\DependencyInjection;

enum TraceSamplerEnum: string
{
    case AlwaysOn = 'always_on';
    case AlwaysOff = 'always_off';
    case TraceIdRation = 'trace_id_ratio';
    case ParentBased = 'parent_based';
}
