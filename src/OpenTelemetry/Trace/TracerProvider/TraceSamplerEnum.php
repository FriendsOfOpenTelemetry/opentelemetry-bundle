<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\TracerProvider;

use OpenTelemetry\SDK\Trace\Sampler\AlwaysOffSampler;
use OpenTelemetry\SDK\Trace\Sampler\AlwaysOnSampler;
use OpenTelemetry\SDK\Trace\Sampler\ParentBased;
use OpenTelemetry\SDK\Trace\Sampler\TraceIdRatioBasedSampler;
use OpenTelemetry\SDK\Trace\SamplerInterface;

enum TraceSamplerEnum: string
{
    case AlwaysOff = 'always_off';
    case AlwaysOn = 'always_on';
    case ParentBased = 'parent_based';
    case TraceIdRatio = 'trace_id_ratio';

    /**
     * @return class-string<SamplerInterface>
     */
    public function getClass(): string
    {
        return match ($this) {
            self::AlwaysOff => AlwaysOffSampler::class,
            self::AlwaysOn => AlwaysOnSampler::class,
            self::ParentBased => ParentBased::class,
            self::TraceIdRatio => TraceIdRatioBasedSampler::class,
        };
    }
}
