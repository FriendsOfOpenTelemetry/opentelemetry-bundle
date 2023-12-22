<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LogExporter;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterFactoryInterface;
use OpenTelemetry\SDK\Logs\LogRecordExporterInterface;

/**
 * @extends ExporterFactoryInterface<LogRecordExporterInterface>
 */
interface LogExporterFactoryInterface extends ExporterFactoryInterface
{
}
