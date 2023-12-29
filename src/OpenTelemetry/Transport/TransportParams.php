<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Transport;

final readonly class TransportParams
{
    /**
     * @param array<string, mixed> $headers
     */
    public function __construct(
        public ?string $contentType = 'application/json',
        public array $headers = [],
        public ?string $compression = 'none',
        public float $timeout = .10,
        public int $retryDelay = 100,
        public int $maxRetries = 3,
        public ?string $caCert = null,
        public ?string $cert = null,
        public ?string $key = null
    ) {
    }
}
