<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Transport;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterEndpointInterface;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterOptionsInterface;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\OtlpExporterCompressionEnum;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\OtlpExporterFormatEnum;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\OtlpExporterOptions;
use OpenTelemetry\SDK\Common\Export\TransportInterface;

final readonly class StreamTransportFactory implements TransportFactoryInterface
{
    private function __construct(
        private string $endpoint,
        private TransportParams $params,
    ) {
    }

    public static function fromExporter(ExporterEndpointInterface $endpoint, ExporterOptionsInterface $options): self
    {
        if (false === self::supportExporter($endpoint, $options)) {
            throw new \RuntimeException('Unsupported exporter endpoint or options for this transport.');
        }

        $params = new TransportParams();
        if ($options instanceof OtlpExporterOptions) {
            $params = TransportParams::fromOtlpExporterOptions($options);
        }

        return new self((string) $endpoint, $params);
    }

    public static function supportExporter(ExporterEndpointInterface $endpoint, ExporterOptionsInterface $options): bool
    {
        return 'stream' === $endpoint->getTransport();
    }

    public function create(): TransportInterface
    {
        $format = OtlpExporterFormatEnum::tryFrom($this->params->contentType) ?? OtlpExporterFormatEnum::Json;
        $compression = OtlpExporterCompressionEnum::tryFrom($this->params->compression) ?? OtlpExporterCompressionEnum::None;

        return (new \OpenTelemetry\SDK\Common\Export\Stream\StreamTransportFactory())->create(
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