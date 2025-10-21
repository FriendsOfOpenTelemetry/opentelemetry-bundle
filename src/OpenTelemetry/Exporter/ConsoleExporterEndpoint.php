<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter;

final readonly class ConsoleExporterEndpoint implements ExporterEndpointInterface
{
    private function __construct(private ExporterDsn $dsn)
    {
        if ('console' !== $this->dsn->getExporter()) {
            throw new \RuntimeException('Provided DSN exporter is not compatible with this endpoint.');
        }
    }

    public static function fromDsn(ExporterDsn $dsn): ExporterEndpointInterface
    {
        return new self($dsn);
    }

    public function __toString()
    {
        return $this->dsn->getPath() ?? 'php://stdout';
    }

    public function getTransport(): ?string
    {
        return null;
    }

    public function getExporter(): string
    {
        return $this->dsn->getExporter();
    }

    public function getDsn(): ExporterDsn
    {
        return $this->dsn;
    }
}
