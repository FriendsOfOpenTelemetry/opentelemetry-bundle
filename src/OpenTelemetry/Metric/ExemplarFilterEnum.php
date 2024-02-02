<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric;

enum ExemplarFilterEnum: string
{
    case All = 'all';
    case None = 'none';
    case WithSampledTrace = 'with_sampled_trace';
}
