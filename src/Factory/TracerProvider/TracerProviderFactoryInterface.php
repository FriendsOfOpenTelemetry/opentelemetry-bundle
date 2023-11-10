<?php

namespace GaelReyrol\OpenTelemetryBundle\Factory\TracerProvider;

use OpenTelemetry\SDK\Trace\SamplerInterface;
use OpenTelemetry\SDK\Trace\SpanProcessorInterface;
use OpenTelemetry\SDK\Trace\TracerProviderInterface;

interface TracerProviderFactoryInterface
{
    /**
     * @param array{
     *      processors: SpanProcessorInterface[],
     *      sampler: SamplerInterface,
     *  } $options
     */
    public static function createFromOptions(array $options): TracerProviderInterface;
}
