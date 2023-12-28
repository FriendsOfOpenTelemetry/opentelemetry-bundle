<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LogExporter;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterDsn;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterOptionsInterface;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LogExporterEndpoint;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Transport\StreamTransportFactory;
use OpenTelemetry\SDK\Logs\Exporter\ConsoleExporter;

final class ConsoleLogExporterFactory implements LogExporterFactoryInterface
{
    public static function createExporter(ExporterDsn $dsn, ExporterOptionsInterface $options): ConsoleExporter
    {
        $transportFactory = StreamTransportFactory::fromExporter(LogExporterEndpoint::fromDsn($dsn), $options);

        return new ConsoleExporter($transportFactory->createTransport());
    }
}
