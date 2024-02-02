<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MeterProvider;

use Psr\Log\LoggerInterface;

abstract class AbstractMeterProviderFactory implements MeterProviderFactoryInterface
{
    public function __construct(protected ?LoggerInterface $logger = null)
    {
    }
}
