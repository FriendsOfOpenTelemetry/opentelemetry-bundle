<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\TracerProvider;

use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SDK\Trace\SamplerInterface;
use OpenTelemetry\SDK\Trace\TracerProvider;
use OpenTelemetry\SDK\Trace\TracerProviderInterface;

final readonly class DefaultTracerProviderFactory extends AbstractTracerProviderFactory
{
    public function createProvider(?SamplerInterface $sampler = null, array $processors = [], ?ResourceInfo $info = null): TracerProviderInterface
    {
        if (0 >= count($processors)) {
            throw new \InvalidArgumentException('Processors should not be empty');
        }

        return new TracerProvider($processors, $sampler, $info);
    }
}
