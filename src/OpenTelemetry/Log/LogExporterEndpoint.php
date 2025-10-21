<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ConsoleExporterEndpoint;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterDsn;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterEndpointInterface;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\OtlpExporterEndpoint;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LogExporter\LogExporterEnum;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Transport\TransportEnum;
use OpenTelemetry\API\Signals;

final class LogExporterEndpoint implements ExporterEndpointInterface
{
    private LogExporterEnum $exporter;

    private ?TransportEnum $transport;

    private function __construct(
        private readonly ExporterDsn $dsn,
    ) {
        $this->exporter = LogExporterEnum::fromDsn($dsn);
        $this->transport = TransportEnum::fromDsn($dsn);
    }

    public static function fromDsn(ExporterDsn $dsn): self
    {
        return new self($dsn);
    }

    public function __toString()
    {
        if (LogExporterEnum::Console === $this->exporter) {
            return (string) ConsoleExporterEndpoint::fromDsn($this->dsn);
        }

        if (LogExporterEnum::Otlp === $this->exporter) {
            return (string) OtlpExporterEndpoint::fromDsn($this->dsn)->withSignal(Signals::LOGS);
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
