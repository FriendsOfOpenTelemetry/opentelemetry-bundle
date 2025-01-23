<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Transport;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterEndpointInterface;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterOptionsInterface;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\OtlpExporterCompressionEnum;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\OtlpExporterFormatEnum;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;
use OpenTelemetry\SDK\Common\Export\Http\PsrTransport;
use OpenTelemetry\SDK\Common\Export\Http\PsrUtils;
use OpenTelemetry\SDK\Common\Export\TransportInterface;

final readonly class PsrHttpTransportFactory implements TransportFactoryInterface
{
    public function supports(#[\SensitiveParameter] ExporterEndpointInterface $endpoint, ExporterOptionsInterface $options): bool
    {
        return null !== $endpoint->getTransport()
            && in_array(TransportEnum::tryFrom($endpoint->getTransport()), [TransportEnum::Http, TransportEnum::Https], true);
    }

    public function createTransport(#[\SensitiveParameter] ExporterEndpointInterface $endpoint, ExporterOptionsInterface $options): TransportInterface
    {
        $params = $options->toTransportParams();
        $format = $params->contentType ?? OtlpExporterFormatEnum::Json->toContentType();
        $compression = OtlpExporterCompressionEnum::tryFrom($params->compression) ?? OtlpExporterCompressionEnum::None;

        return new PsrTransport(
            new Client(),
            new HttpFactory(),
            new HttpFactory(),
            (string) $endpoint,
            $format,
            $params->headers,
            PsrUtils::compression($compression->toKnownValue()),
            $params->retryDelay,
            $params->maxRetries,
        );
    }
}
