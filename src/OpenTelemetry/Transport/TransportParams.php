<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Transport;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\OtlpExporterOptions;

final readonly class TransportParams
{
    /**
     * @param array<string, mixed> $headers
     */
    public function __construct(
        public ?string $contentType = 'application/json',
        public array $headers = [],
        public ?string $compression = 'none',
        public float $timeout = 10.,
        public int $retryDelay = 100,
        public int $maxRetries = 3,
        public ?string $caCert = null,
        public ?string $cert = null,
        public ?string $key = null
    ) {
    }

    public static function fromOtlpExporterOptions(OtlpExporterOptions $options): self
    {
        return new self(
            contentType: $options->getFormat()->toContentType(),
            headers: $options->getHeaders(),
            compression: $options->getCompression()->toKnownValue(),
            timeout: $options->getTimeout(),
            retryDelay: $options->getRetryDelay(),
            maxRetries: $options->getMaxRetries(),
            caCert: $options->getCaCertificate(),
            cert: $options->getCertificate(),
            key: $options->getKey(),
        );
    }
}
