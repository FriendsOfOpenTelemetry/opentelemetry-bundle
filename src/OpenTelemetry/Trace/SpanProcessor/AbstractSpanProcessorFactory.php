<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\SpanProcessor;

use Psr\Log\LoggerInterface;

abstract class AbstractSpanProcessorFactory implements SpanProcessorFactoryInterface
{
    public function __construct(private ?LoggerInterface $logger = null)
    {
    }
}
