<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Transport;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterEndpointInterface;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterOptionsInterface;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\OtlpExporterCompressionEnum;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\OtlpExporterFormatEnum;
use OpenTelemetry\SDK\Common\Export\TransportInterface;

final readonly class OtlpHttpTransportFactory implements TransportFactoryInterface
{
    public function supports(#[\SensitiveParameter] ExporterEndpointInterface $endpoint, ExporterOptionsInterface $options): bool
    {
        if (null === $endpoint->getTransport()) {
            return false;
        }

        if ('otlp' !== $endpoint->getExporter()) {
            return false;
        }

        return in_array(TransportEnum::tryFrom($endpoint->getTransport()), [TransportEnum::Http, TransportEnum::Https], true);
    }

    public function createTransport(#[\SensitiveParameter] ExporterEndpointInterface $endpoint, ExporterOptionsInterface $options): TransportInterface
    {
        $params = $options->toTransportParams();
        $format = $params->contentType ?? OtlpExporterFormatEnum::Json->toContentType();
        $compression = OtlpExporterCompressionEnum::tryFrom($params->compression) ?? OtlpExporterCompressionEnum::None;

        return (new \OpenTelemetry\Contrib\Otlp\OtlpHttpTransportFactory())->create(
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
