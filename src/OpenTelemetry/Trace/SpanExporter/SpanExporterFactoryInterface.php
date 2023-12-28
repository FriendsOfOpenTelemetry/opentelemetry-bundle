<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\SpanExporter;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterFactoryInterface;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;

/**
 * @extends ExporterFactoryInterface<SpanExporterInterface>
 */
interface SpanExporterFactoryInterface extends ExporterFactoryInterface
{
}
