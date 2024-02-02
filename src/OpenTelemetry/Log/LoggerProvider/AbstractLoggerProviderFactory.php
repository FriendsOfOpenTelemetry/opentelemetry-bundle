<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LoggerProvider;

use Psr\Log\LoggerInterface;

abstract class AbstractLoggerProviderFactory implements LoggerProviderFactoryInterface
{
    public function __construct(protected ?LoggerInterface $logger = null)
    {
    }
}
