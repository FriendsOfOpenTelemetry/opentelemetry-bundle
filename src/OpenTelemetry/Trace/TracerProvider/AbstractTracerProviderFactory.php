<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\TracerProvider;

use Psr\Log\LoggerInterface;

abstract class AbstractTracerProviderFactory implements TracerProviderFactoryInterface
{
    public function __construct(protected ?LoggerInterface $logger = null)
    {
    }
}
