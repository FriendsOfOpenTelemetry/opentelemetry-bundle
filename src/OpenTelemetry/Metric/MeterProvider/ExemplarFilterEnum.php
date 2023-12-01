<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MeterProvider;

enum ExemplarFilterEnum: string
{
    case WithSampledTrace = 'with_sampled_trace';
    case All = 'all';
    case None = 'none';
}
