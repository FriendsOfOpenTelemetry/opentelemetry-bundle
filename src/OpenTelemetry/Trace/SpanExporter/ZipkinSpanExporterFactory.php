<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\SpanExporter;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterDsn;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterOptionsInterface;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\TraceExporterEndpoint;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Transport\PsrHttpTransportFactory;
use OpenTelemetry\Contrib\Zipkin\Exporter;

final readonly class ZipkinSpanExporterFactory implements SpanExporterFactoryInterface
{
    public static function createExporter(ExporterDsn $dsn, ExporterOptionsInterface $options): Exporter
    {
        $exporter = TraceExporterEnum::fromDsn($dsn);
        if (TraceExporterEnum::Zipkin !== $exporter) {
            throw new \InvalidArgumentException('DSN exporter must be of type Zipkin.');
        }

        $transportFactory = PsrHttpTransportFactory::fromExporter(TraceExporterEndpoint::fromDsn($dsn), $options);

        return new Exporter($transportFactory->createTransport());
    }
}
