<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MeterProvider;

use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilter\AllExemplarFilter;
use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilter\NoneExemplarFilter;
use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilter\WithSampledTraceExemplarFilter;
use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilterInterface;

enum ExemplarFilterEnum: string
{
    case All = 'all';
    case None = 'none';
    case WithSampledTrace = 'with_sampled_trace';

    /**
     * @return class-string<ExemplarFilterInterface>
     */
    public function getClass(): string
    {
        return match ($this) {
            self::All => AllExemplarFilter::class,
            self::None => NoneExemplarFilter::class,
            self::WithSampledTrace => WithSampledTraceExemplarFilter::class,
        };
    }
}
