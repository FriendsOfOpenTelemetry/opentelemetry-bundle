<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MetricExporter;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterFactoryInterface;
use OpenTelemetry\SDK\Metrics\MetricExporterInterface;

/**
 * @extends ExporterFactoryInterface<MetricExporterInterface>
 */
interface MetricExporterFactoryInterface extends ExporterFactoryInterface
{
}
