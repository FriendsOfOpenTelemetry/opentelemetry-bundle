<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter;

use OpenTelemetry\Contrib\Otlp\OtlpUtil;

final class OtlpExporterOptions implements ExporterOptionsInterface
{
    /** @var float */
    public const DEFAULT_TIMEOUT = .10;

    /** @var int */
    public const DEFAULT_RETRY_DELAY = 100;

    /** @var int */
    public const DEFAULT_MAX_RETRIES = 3;

    /**
     * @param array<string, mixed> $headers
     */
    private function __construct(
        private OtlpExporterFormatEnum $format = OtlpExporterFormatEnum::Json,
        private array $headers = [],
        private OtlpExporterCompressionEnum $compression = OtlpExporterCompressionEnum::None,
        private float $timeout = self::DEFAULT_TIMEOUT,
        private int $retryDelay = self::DEFAULT_RETRY_DELAY,
        private int $maxRetries = self::DEFAULT_MAX_RETRIES,
        private ?string $caCertificate = null,
        private ?string $certificate = null,
        private ?string $key = null,
    ) {
    }

    public static function fromConfiguration(array $configuration): self
    {
        $options = new self();

        if (isset($configuration['format']) && null !== OtlpExporterFormatEnum::tryFrom($configuration['format'])) {
            $options->format = OtlpExporterFormatEnum::from($configuration['format']);
        }

        if (isset($configuration['headers'])) {
            $options->headers = $configuration['headers'];
        }

        if (isset($configuration['compression']) && null !== OtlpExporterCompressionEnum::tryFrom($configuration['compression'])) {
            $options->compression = OtlpExporterCompressionEnum::from($configuration['compression']);
        }

        if (isset($configuration['timeout'])) {
            $options->timeout = $configuration['timeout'];
        }

        if (isset($configuration['retry'])) {
            $options->retryDelay = $configuration['retry'];
        }

        if (isset($configuration['max'])) {
            $options->maxRetries = $configuration['max'];
        }

        if (isset($configuration['ca'])) {
            $options->caCertificate = $configuration['ca'];
        }

        if (isset($configuration['cert'])) {
            $options->certificate = $configuration['cert'];
        }

        if (isset($configuration['key'])) {
            $options->key = $configuration['key'];
        }

        return $options;
    }

    public function getFormat(): OtlpExporterFormatEnum
    {
        return $this->format;
    }

    /**
     * @return array<string, mixed>
     */
    public function getHeaders(): array
    {
        return $this->headers + OtlpUtil::getUserAgentHeader();
    }

    public function getCompression(): OtlpExporterCompressionEnum
    {
        return $this->compression;
    }

    public function getTimeout(): float
    {
        return $this->timeout;
    }

    public function getRetryDelay(): int
    {
        return $this->retryDelay;
    }

    public function getMaxRetries(): int
    {
        return $this->maxRetries;
    }

    public function getCaCertificate(): ?string
    {
        return $this->caCertificate;
    }

    public function getCertificate(): ?string
    {
        return $this->certificate;
    }

    public function getKey(): ?string
    {
        return $this->key;
    }
}
