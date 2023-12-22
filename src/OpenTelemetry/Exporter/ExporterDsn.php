<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter;

final class ExporterDsn
{
    public function __construct(
        private readonly string $scheme,
        private readonly string $host,
        private readonly ?string $user = null,
        #[\SensitiveParameter]
        private readonly ?string $password = null,
        private readonly ?int $port = null,
        private readonly ?string $path = null,
        /**
         * @var array<string, mixed>
         */
        private readonly array $options = []
    ) {
    }

    public static function fromString(#[\SensitiveParameter] string $dsn): self
    {
        if (false === $parsedDsn = parse_url($dsn)) {
            throw new \InvalidArgumentException('The DSN is invalid.');
        }

        if (!isset($parsedDsn['scheme'])) {
            throw new \InvalidArgumentException('The DSN must contain a scheme.');
        }

        if (!isset($parsedDsn['host'])) {
            throw new \InvalidArgumentException('The DSN must contain a host (use "default" by default).');
        }

        $user = '' !== ($parsedDsn['user'] ?? '') ? urldecode($parsedDsn['user']) : null;
        $password = '' !== ($parsedDsn['pass'] ?? '') ? urldecode($parsedDsn['pass']) : null;
        $port = $parsedDsn['port'] ?? null;
        $path = $parsedDsn['path'] ?? null;
        parse_str($parsedDsn['query'] ?? '', $query);

        return new self($parsedDsn['scheme'], $parsedDsn['host'], $user, $password, $port, $path, $query);
    }

    public function getScheme(): string
    {
        return $this->scheme;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getUser(): ?string
    {
        return $this->user;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function getPort(int $default = null): ?int
    {
        return $this->port ?? $default;
    }

    public function getOption(string $key, mixed $default = null): mixed
    {
        return $this->options[$key] ?? $default;
    }

    /**
     * @return string[]
     */
    private function parseScheme(): array
    {
        return explode('+', $this->getScheme(), 2);
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
            throw new \InvalidArgumentException('The DSN scheme must contain an exporter.');
        }

        return $exporter;
    }

    public function getTransport(): ?string
    {
        $parts = $this->parseScheme();

        return 2 === count($parts) ? $parts[0] : null;
    }
}
