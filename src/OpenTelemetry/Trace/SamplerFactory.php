<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace;

use OpenTelemetry\SDK\Trace\Sampler\AlwaysOffSampler;
use OpenTelemetry\SDK\Trace\Sampler\AlwaysOnSampler;
use OpenTelemetry\SDK\Trace\Sampler\ParentBased;
use OpenTelemetry\SDK\Trace\Sampler\TraceIdRatioBasedSampler;
use OpenTelemetry\SDK\Trace\SamplerInterface;

final class SamplerFactory
{
    public static function create(string $name, ?float $probability = null): SamplerInterface
    {
        $sampler = TraceSamplerEnum::tryFrom($name);

        return match ($sampler) {
            TraceSamplerEnum::AlwaysOn => new AlwaysOnSampler(),
            TraceSamplerEnum::AlwaysOff => new AlwaysOffSampler(),
            TraceSamplerEnum::ParentBasedAlwaysOn => new ParentBased(new AlwaysOnSampler()),
            TraceSamplerEnum::ParentBasedAlwaysOff => new ParentBased(new AlwaysOffSampler()),
            TraceSamplerEnum::ParentBasedTraceIdRatio => new ParentBased(new TraceIdRatioBasedSampler($probability)),
            TraceSamplerEnum::TraceIdRatio => new TraceIdRatioBasedSampler($probability),
            default => throw new \InvalidArgumentException(sprintf('Unknown sampler: %s', $name)),
        };
    }
}
