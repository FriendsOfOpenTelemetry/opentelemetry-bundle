<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterDsn;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterEndpointInterface;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\SpanExporter\TraceExporterEnum;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Transport\TransportEnum;
use Http\Discovery\Psr17FactoryDiscovery;
use Psr\Http\Message\UriFactoryInterface;

final class ZipkinExporterEndpoint implements ExporterEndpointInterface
{
    private UriFactoryInterface $uriFactory;
    private TransportEnum $transport;

    private function __construct(
        private readonly ExporterDsn $dsn,
        UriFactoryInterface $uriFactory = null,
    ) {
        if (TraceExporterEnum::Zipkin !== TraceExporterEnum::from($this->dsn->getExporter())) {
            throw new \RuntimeException('Provided DSN exporter is not compatible with this endpoint.');
        }
        $this->uriFactory = $uriFactory ?? Psr17FactoryDiscovery::findUriFactory();
        $this->transport = TransportEnum::from($this->dsn->getTransport());
    }

    public static function fromDsn(ExporterDsn $dsn): self
    {
        return new self($dsn);
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
}
