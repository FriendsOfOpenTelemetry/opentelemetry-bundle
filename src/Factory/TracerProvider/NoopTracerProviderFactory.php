<?php

namespace GaelReyrol\OpenTelemetryBundle\Factory\TracerProvider;

use OpenTelemetry\SDK\Trace\NoopTracerProvider;
use OpenTelemetry\SDK\Trace\TracerProviderInterface;

final class NoopTracerProviderFactory implements TracerProviderFactoryInterface
{
    public static function createFromOptions(array $options): TracerProviderInterface
    {
        return new NoopTracerProvider();
    }
}
