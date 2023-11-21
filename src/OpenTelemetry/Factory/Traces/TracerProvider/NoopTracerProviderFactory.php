<?php

namespace GaelReyrol\OpenTelemetryBundle\OpenTelemetry\Factory\Traces\TracerProvider;

use OpenTelemetry\SDK\Trace\NoopTracerProvider;
use OpenTelemetry\SDK\Trace\SamplerInterface;
use OpenTelemetry\SDK\Trace\TracerProviderInterface;

final class NoopTracerProviderFactory implements TracerProviderFactoryInterface
{
    public static function create(SamplerInterface $sampler, array $processors): TracerProviderInterface
    {
        return new NoopTracerProvider();
    }
}
