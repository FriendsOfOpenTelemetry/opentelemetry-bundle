<?php

namespace GaelReyrol\OpenTelemetryBundle\OpenTelemetry\Trace\TracerProvider;

use OpenTelemetry\SDK\Trace\SamplerInterface;
use OpenTelemetry\SDK\Trace\TracerProvider;
use OpenTelemetry\SDK\Trace\TracerProviderInterface;

final readonly class TracerProviderFactory implements TracerProviderFactoryInterface
{
    public static function create(SamplerInterface $sampler = null, array $processors = null): TracerProviderInterface
    {
        if (null === $processors || 0 === count($processors)) {
            throw new \InvalidArgumentException('Processors should not be empty');
        }

        return new TracerProvider($processors, $sampler);
    }
}
