<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\SpanExporter;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Transport\TransportFactoryInterface;
use Psr\Log\LoggerInterface;

abstract readonly class AbstractSpanExporterFactory implements SpanExporterFactoryInterface
{
    public function __construct(
        protected TransportFactoryInterface $transportFactory,
        protected ?LoggerInterface $logger = null
    ) {
    }
}
