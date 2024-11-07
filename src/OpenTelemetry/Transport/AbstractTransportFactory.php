<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Transport;

use Psr\Log\LoggerInterface;

abstract readonly class AbstractTransportFactory implements TransportFactoryInterface
{
    public function __construct(private ?LoggerInterface $logger = null)
    {
    }
}
