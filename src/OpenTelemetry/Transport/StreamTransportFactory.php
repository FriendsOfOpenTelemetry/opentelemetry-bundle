<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Transport;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterEndpointInterface;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterOptionsInterface;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\OtlpExporterCompressionEnum;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\OtlpExporterFormatEnum;
use OpenTelemetry\SDK\Common\Export\TransportInterface;

final readonly class StreamTransportFactory implements TransportFactoryInterface
{
    public function supports(#[\SensitiveParameter] ExporterEndpointInterface $endpoint, ExporterOptionsInterface $options): bool
    {
        return null !== $endpoint->getTransport()
            && TransportEnum::Stream === TransportEnum::tryFrom($endpoint->getTransport());
    }

    public function createTransport(#[\SensitiveParameter] ExporterEndpointInterface $endpoint, ExporterOptionsInterface $options): TransportInterface
    {
        $params = $options->toTransportParams();
        $format = $params->contentType ?? OtlpExporterFormatEnum::Json->toContentType();
        $compression = OtlpExporterCompressionEnum::tryFrom($params->compression) ?? OtlpExporterCompressionEnum::None;

        return (new \OpenTelemetry\SDK\Common\Export\Stream\StreamTransportFactory())->create(
            (string) $endpoint,
            $format,
            $params->headers,
            $compression->toKnownValue(),
            $params->timeout,
            $params->retryDelay,
            $params->maxRetries,
            $params->caCert,
            $params->cert,
            $params->key,
        );
    }
}
