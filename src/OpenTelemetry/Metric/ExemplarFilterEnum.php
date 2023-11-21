<?php

namespace GaelReyrol\OpenTelemetryBundle\OpenTelemetry\Metric;

enum ExemplarFilterEnum: string
{
    case WithSampledTrace = 'with_sampled_trace';
    case All = 'all';
    case None = 'none';
}
