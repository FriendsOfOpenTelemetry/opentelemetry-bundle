<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Transport\TransportEnum;
use Http\Discovery\Psr17FactoryDiscovery;
use OpenTelemetry\API\Signals;
use OpenTelemetry\Contrib\Otlp\HttpEndpointResolverInterface;
use OpenTelemetry\Contrib\Otlp\OtlpUtil;
use Psr\Http\Message\UriFactoryInterface;

final class OtlpExporterEndpoint implements ExporterEndpointInterface
{
    private UriFactoryInterface $uriFactory;
    private ?string $signal = null;
    private TransportEnum $transport;

    private function __construct(
        private readonly ExporterDsn $dsn,
        UriFactoryInterface $uriFactory = null,
    ) {
        if ('otlp' !== $this->dsn->getExporter()) {
            throw new \RuntimeException('Provided DSN exporter is not compatible with this endpoint.');
        }
        $this->uriFactory = $uriFactory ?? Psr17FactoryDiscovery::findUriFactory();
        $this->transport = TransportEnum::from($this->dsn->getTransport());
    }

    public static function fromDsn(ExporterDsn $dsn): self
    {
        return new self($dsn);
    }

    public function withSignal(string $signal): self
    {
        if (false === in_array($signal, Signals::SIGNALS, true)) {
            throw new \RuntimeException('Provided Otlp signal is invalid.');
        }

        $this->signal = $signal;

        return $this;
    }

    public function __toString()
    {
        $uri = $this->uriFactory->createUri();
        $uri = $uri
            ->withScheme($this->transport->getScheme())
            ->withHost($this->dsn->getHost())
            ->withPort($this->dsn->getPort() ?? $this->transport->getPort())
            ->withPath($this->dsn->getPath() ?? $this->getPath());

        if (null !== $this->dsn->getUser()) {
            $uri = $uri->withUserInfo($this->dsn->getUser(), $this->dsn->getPassword());
        }

        return (string) $uri;
    }

    private function getPath(): string
    {
        if (null === $this->signal) {
            throw new \RuntimeException('Signal for Otlp endpoint was not provided');
        }

        return match ($this->transport) {
            TransportEnum::Grpc, TransportEnum::Grpcs => OtlpUtil::method($this->signal),
            TransportEnum::Http, TransportEnum::Https => HttpEndpointResolverInterface::DEFAULT_PATHS[$this->signal],
            default => '',
        };
    }

    public function getTransport(): ?string
    {
        return $this->transport->value;
    }

    public function getExporter(): string
    {
        return 'otlp';
    }
}
