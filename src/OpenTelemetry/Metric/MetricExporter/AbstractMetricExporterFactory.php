<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MetricExporter;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Transport\TransportFactoryInterface;
use Psr\Log\LoggerInterface;

abstract class AbstractMetricExporterFactory implements MetricExporterFactoryInterface
{
    public function __construct(
        protected TransportFactoryInterface $transportFactory,
        protected ?LoggerInterface $logger = null,
    ) {
    }
}
