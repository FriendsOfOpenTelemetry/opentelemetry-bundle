<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LogExporter;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Transport\TransportFactoryInterface;
use Psr\Log\LoggerInterface;

abstract class AbstractLogExporterFactory implements LogExporterFactoryInterface
{
    public function __construct(
        protected TransportFactoryInterface $transportFactory,
        protected ?LoggerInterface $logger = null
    ) {
    }
}
