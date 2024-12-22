<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\TracerProvider;

use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SDK\Trace\NoopTracerProvider;
use OpenTelemetry\SDK\Trace\SamplerInterface;
use OpenTelemetry\SDK\Trace\TracerProviderInterface;

final readonly class NoopTracerProviderFactory extends AbstractTracerProviderFactory
{
    public function createProvider(?SamplerInterface $sampler = null, array $processors = [], ?ResourceInfo $info = null): TracerProviderInterface
    {
        return new NoopTracerProvider();
    }
}
