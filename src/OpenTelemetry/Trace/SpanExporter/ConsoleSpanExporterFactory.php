<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\SpanExporter;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterDsn;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterOptionsInterface;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\TraceExporterEndpoint;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Transport\StreamTransportFactory;
use OpenTelemetry\SDK\Trace\SpanExporter\ConsoleSpanExporter;

final readonly class ConsoleSpanExporterFactory implements SpanExporterFactoryInterface
{
    public static function createExporter(ExporterDsn $dsn, ExporterOptionsInterface $options): ConsoleSpanExporter
    {
        $transport = StreamTransportFactory::fromExporter(TraceExporterEndpoint::fromDsn($dsn), $options)->createTransport();

        return new ConsoleSpanExporter($transport);
    }
}
