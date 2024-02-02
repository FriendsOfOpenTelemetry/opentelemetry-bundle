<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\SpanProcessor;

use Psr\Log\LoggerInterface;

abstract class AbstractSpanProcessorFactory implements SpanProcessorFactoryInterface
{
    /** @phpstan-ignore-next-line */
    public function __construct(private ?LoggerInterface $logger = null)
    {
    }
}
