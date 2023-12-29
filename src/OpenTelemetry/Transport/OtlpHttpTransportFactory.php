<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Transport;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterEndpointInterface;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterOptionsInterface;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\OtlpExporterCompressionEnum;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\OtlpExporterFormatEnum;
use OpenTelemetry\SDK\Common\Export\TransportInterface;

final readonly class OtlpHttpTransportFactory implements TransportFactoryInterface
{
    private function __construct(
        private string $endpoint,
        private TransportParams $params,
    ) {
    }

    public static function fromExporter(ExporterEndpointInterface $endpoint, ExporterOptionsInterface $options): self
    {
        if (false === self::supportExporter($endpoint, $options)) {
            throw new \InvalidArgumentException('Unsupported exporter endpoint or options for this transport.');
        }

        return new self((string) $endpoint, $options->toTransportParams());
    }

    public static function supportExporter(ExporterEndpointInterface $endpoint, ExporterOptionsInterface $options): bool
    {
        if (false === str_contains($endpoint->getTransport() ?? '', 'http')) {
            return false;
        }

        if ('otlp' !== $endpoint->getExporter()) {
            return false;
        }

        return true;
    }

    public function createTransport(): TransportInterface
    {
        $format = OtlpExporterFormatEnum::tryFrom($this->params->contentType) ?? OtlpExporterFormatEnum::Json;
        $compression = OtlpExporterCompressionEnum::tryFrom($this->params->contentType) ?? OtlpExporterCompressionEnum::None;

        return (new \OpenTelemetry\Contrib\Otlp\OtlpHttpTransportFactory())->create(
            $this->endpoint,
            $format->toContentType(),
            $this->params->headers,
            $compression->toKnownValue(),
            $this->params->timeout,
            $this->params->retryDelay,
            $this->params->maxRetries,
            $this->params->caCert,
            $this->params->cert,
            $this->params->key,
        );
    }
}
