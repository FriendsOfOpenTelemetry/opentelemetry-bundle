<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\TracerProvider;

use OpenTelemetry\SDK\Trace\SamplerInterface;
use OpenTelemetry\SDK\Trace\SpanProcessorInterface;
use OpenTelemetry\SDK\Trace\TracerProviderInterface;

interface TracerProviderFactoryInterface
{
    /**
     * @param SpanProcessorInterface[] $processors
     */
    public static function createProvider(SamplerInterface $sampler = null, array $processors = []): TracerProviderInterface;
}
