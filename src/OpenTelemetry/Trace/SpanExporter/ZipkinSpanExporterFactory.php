<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\SpanExporter;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterDsn;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterOptionsInterface;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\TraceExporterEndpoint;
use OpenTelemetry\Contrib\Zipkin\Exporter;

final readonly class ZipkinSpanExporterFactory extends AbstractSpanExporterFactory
{
    public function supports(#[\SensitiveParameter] ExporterDsn $dsn, ExporterOptionsInterface $options): bool
    {
        return TraceExporterEnum::Zipkin === TraceExporterEnum::tryFrom($dsn->getExporter());
    }

    public function createExporter(#[\SensitiveParameter] ExporterDsn $dsn, ExporterOptionsInterface $options): Exporter
    {
        return new Exporter($this->transportFactory->createTransport(
            TraceExporterEndpoint::fromDsn($dsn),
            $options,
        ));
    }
}
