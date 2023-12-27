<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ConsoleExporterEndpoint;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterDsn;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterEndpointInterface;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\OtlpExporterEndpoint;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\SpanExporter\TraceExporterEnum;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Transport\TransportEnum;
use OpenTelemetry\API\Signals;

final class TraceExporterEndpoint implements ExporterEndpointInterface
{
    private TraceExporterEnum $exporter;
    private ?TransportEnum $transport;

    private function __construct(private readonly ExporterDsn $dsn)
    {
        $this->exporter = TraceExporterEnum::fromDsn($this->dsn);
        $this->transport = TransportEnum::fromDsn($this->dsn);
    }

    public static function fromDsn(ExporterDsn $dsn): self
    {
        return new self($dsn);
    }

    public function __toString()
    {
        if (TraceExporterEnum::Console === $this->exporter) {
            return (string) ConsoleExporterEndpoint::fromDsn($this->dsn);
        }

        if (TraceExporterEnum::Zipkin === $this->exporter) {
            return (string) ZipkinExporterEndpoint::fromDsn($this->dsn);
        }

        if (TraceExporterEnum::Otlp === $this->exporter) {
            return (string) OtlpExporterEndpoint::fromDsn($this->dsn)->withSignal(Signals::TRACE);
        }

        if (in_array($this->exporter, [TraceExporterEnum::InMemory], true)) {
            return '';
        }

        throw new \RuntimeException('Unsupported DSN for Trace endpoint');
    }

    public function getTransport(): ?string
    {
        return $this->transport?->value;
    }
}
