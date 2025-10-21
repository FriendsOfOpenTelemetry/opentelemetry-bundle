<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter;

use Zenstruck\Dsn;
use Zenstruck\Uri;
use Zenstruck\Uri\Part\Query;

final class ExporterDsn
{
    private function __construct(
        private readonly Uri $uri,
    ) {
    }

    public static function fromString(#[\SensitiveParameter] string $dsn): self
    {
        try {
            $parsedDsn = Dsn::parse($dsn);
        } catch (Dsn\Exception\UnableToParse $exception) {
            throw new \InvalidArgumentException('The DSN is invalid.', previous: $exception);
        }

        if (false === $parsedDsn instanceof Uri) {
            throw new \InvalidArgumentException('The DSN is not an Uri.');
        }

        if (true === $parsedDsn->scheme()->isEmpty()) {
            throw new \InvalidArgumentException('The DSN must contain a scheme.');
        }

        if (true === $parsedDsn->host()->isEmpty()) {
            throw new \InvalidArgumentException('The DSN must contain a host (use "default" by default).');
        }

        return new self($parsedDsn);
    }

    public function getHost(): string
    {
        return $this->uri->host()->toString();
    }

    public function getUser(): ?string
    {
        return $this->uri->username();
    }

    public function getPassword(): ?string
    {
        return $this->uri->password();
    }

    public function getPath(): ?string
    {
        return $this->uri->path()->isEmpty() ? null : $this->uri->path()->toString();
    }

    public function getPort(?int $default = null): ?int
    {
        return $this->uri->port() ?? $default;
    }

    public function getQuery(): Query
    {
        return $this->uri->query();
    }

    /**
     * @return string[]
     */
    private function parseScheme(): array
    {
        return $this->uri->scheme()->segments();
    }

    public function getExporter(): string
    {
        $parts = $this->parseScheme();

        $exporter = null;
        if (2 === count($parts)) {
            $exporter = $parts[1];
        }
        if (1 === count($parts)) {
            $exporter = $parts[0];
        }

        if ('' === $exporter || null === $exporter) {
            throw new \InvalidArgumentException('The DSN scheme does not contain an exporter.');
        }

        return $exporter;
    }

    public function getTransport(): ?string
    {
        $parts = $this->parseScheme();

        return 2 === count($parts) ? $parts[0] : null;
    }
}
