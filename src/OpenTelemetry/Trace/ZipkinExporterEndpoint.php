<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterDsn;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterEndpointInterface;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\SpanExporter\TraceExporterEnum;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Transport\TransportEnum;
use GuzzleHttp\Psr7\HttpFactory;
use Psr\Http\Message\UriFactoryInterface;

final class ZipkinExporterEndpoint implements ExporterEndpointInterface
{
    private ?TransportEnum $transport;

    private function __construct(
        private readonly ExporterDsn $dsn,
        private readonly UriFactoryInterface $uriFactory,
    ) {
        if (TraceExporterEnum::Zipkin !== TraceExporterEnum::fromDsn($this->dsn)) {
            throw new \InvalidArgumentException('Unsupported DSN exporter for this endpoint.');
        }
        $this->transport = TransportEnum::fromDsn($this->dsn);
    }

    public static function fromDsn(ExporterDsn $dsn): self
    {
        return new self($dsn, new HttpFactory());
    }

    public function __toString()
    {
        $uri = $this->uriFactory->createUri();
        $uri = $uri
            ->withScheme($this->transport->getScheme())
            ->withHost($this->dsn->getHost())
            ->withPort($this->dsn->getPort() ?? 9411)
            ->withPath($this->dsn->getPath() ?? '/api/v2/spans');

        if (null !== $this->dsn->getUser()) {
            $uri = $uri->withUserInfo($this->dsn->getUser(), $this->dsn->getPassword());
        }

        return (string) $uri;
    }

    public function getTransport(): ?string
    {
        return $this->transport->value;
    }

    public function getExporter(): string
    {
        return 'zipkin';
    }

    public function getDsn(): ExporterDsn
    {
        return $this->dsn;
    }
}
