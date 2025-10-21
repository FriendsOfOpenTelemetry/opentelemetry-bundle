<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ConsoleExporterEndpoint;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterDsn;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterEndpointInterface;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\OtlpExporterEndpoint;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MetricExporter\MetricExporterEnum;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Transport\TransportEnum;
use OpenTelemetry\API\Signals;

final class MetricExporterEndpoint implements ExporterEndpointInterface
{
    private MetricExporterEnum $exporter;
    private ?TransportEnum $transport;

    private function __construct(private readonly ExporterDsn $dsn)
    {
        $this->exporter = MetricExporterEnum::fromDsn($this->dsn);
        $this->transport = TransportEnum::fromDsn($this->dsn);
    }

    public static function fromDsn(ExporterDsn $dsn): self
    {
        return new self($dsn);
    }

    public function __toString()
    {
        if (MetricExporterEnum::Console === $this->exporter) {
            return (string) ConsoleExporterEndpoint::fromDsn($this->dsn);
        }

        if (MetricExporterEnum::Otlp === $this->exporter) {
            return (string) OtlpExporterEndpoint::fromDsn($this->dsn)->withSignal(Signals::METRICS);
        }

        return '';
    }

    public function getTransport(): ?string
    {
        return $this->transport?->value;
    }

    public function getExporter(): string
    {
        return $this->exporter->value;
    }

    public function getDsn(): ExporterDsn
    {
        return $this->dsn;
    }
}
