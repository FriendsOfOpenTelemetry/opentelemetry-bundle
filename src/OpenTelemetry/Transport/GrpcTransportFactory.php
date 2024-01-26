<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Transport;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterEndpointInterface;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterOptionsInterface;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\OtlpExporterCompressionEnum;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\OtlpExporterFormatEnum;
use OpenTelemetry\SDK\Common\Export\TransportInterface;

final readonly class GrpcTransportFactory extends AbstractTransportFactory
{
    public function supports(#[\SensitiveParameter] ExporterEndpointInterface $endpoint, ExporterOptionsInterface $options): bool
    {
        return null !== $endpoint->getTransport()
            && in_array(TransportEnum::tryFrom($endpoint->getTransport()), [TransportEnum::Grpc, TransportEnum::Grpcs], true);
    }

    public function createTransport(#[\SensitiveParameter] ExporterEndpointInterface $endpoint, ExporterOptionsInterface $options): TransportInterface
    {
        $params = $options->toTransportParams();

        $compression = OtlpExporterCompressionEnum::tryFrom($params->compression) ?? OtlpExporterCompressionEnum::None;

        return (new \OpenTelemetry\Contrib\Grpc\GrpcTransportFactory())->create(
            (string) $endpoint,
            OtlpExporterFormatEnum::Grpc->toContentType(),
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
