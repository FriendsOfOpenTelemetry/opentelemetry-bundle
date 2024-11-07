<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LogProcessor;

use Psr\Log\LoggerInterface;

abstract class AbstractLogProcessorFactory implements LogProcessorFactoryInterface
{
    public function __construct(private ?LoggerInterface $logger = null)
    {
    }
}
