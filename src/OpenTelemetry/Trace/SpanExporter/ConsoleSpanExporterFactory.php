<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\SpanExporter;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterDsn;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterOptionsInterface;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\TraceExporterEndpoint;
use OpenTelemetry\SDK\Trace\SpanExporter\ConsoleSpanExporter;

final readonly class ConsoleSpanExporterFactory extends AbstractSpanExporterFactory
{
    public function supports(#[\SensitiveParameter] ExporterDsn $dsn, ExporterOptionsInterface $options): bool
    {
        return TraceExporterEnum::Console === TraceExporterEnum::tryFrom($dsn->getExporter());
    }

    public function createExporter(#[\SensitiveParameter] ExporterDsn $dsn, ExporterOptionsInterface $options): ConsoleSpanExporter
    {
        return new ConsoleSpanExporter($this->transportFactory->createTransport(TraceExporterEndpoint::fromDsn($dsn), $options));
    }
}
