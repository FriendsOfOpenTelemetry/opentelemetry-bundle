<?php

namespace GaelReyrol\OpenTelemetryBundle\Factory\TracerProvider;

use OpenTelemetry\SDK\Trace\TracerProvider;
use OpenTelemetry\SDK\Trace\TracerProviderInterface;

final readonly class TracerProviderFactory implements TracerProviderFactoryInterface
{
    public static function createFromOptions(array $options): TracerProviderInterface
    {
        return new TracerProvider($options['processors'], $options['sampler']);
    }
}
