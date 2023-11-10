<?php

namespace GaelReyrol\OpenTelemetryBundle\Factory\SpanExporter;

use GaelReyrol\OpenTelemetryBundle\DependencyInjection\OtlpExporterCompressionEnum;
use GaelReyrol\OpenTelemetryBundle\DependencyInjection\OtlpExporterFormatEnum;
use OpenTelemetry\API\Signals;
use OpenTelemetry\Contrib\Otlp\HttpEndpointResolver;
use OpenTelemetry\Contrib\Otlp\OtlpUtil;
use OpenTelemetry\Contrib\Otlp\Protocols;
use OpenTelemetry\Contrib\Otlp\SpanExporter;
use OpenTelemetry\SDK\Common\Configuration\KnownValues;
use OpenTelemetry\SDK\Registry;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;

final readonly class OtlpSpanExporterFactory implements SpanExporterFactoryInterface
{
    public static function createFromOptions(array $options): SpanExporterInterface
    {
        $protocol = self::getProtocol($options['format']);
        $contentType = Protocols::contentType($protocol);
        $headers = self::getHeaders($options['headers']);
        $compression = self::getCompression($options['compression']);

        $factoryClass = Registry::transportFactory($protocol);
        $transport = (new $factoryClass())->create(
            self::formatEndPoint($options['endpoint'], $protocol),
            $contentType,
            $headers,
            $compression,
        );

        return new SpanExporter($transport);
    }

    private static function getProtocol(OtlpExporterFormatEnum $format): string
    {
        return match ($format) {
            OtlpExporterFormatEnum::Json => Protocols::HTTP_JSON,
            OtlpExporterFormatEnum::Ndjson => Protocols::HTTP_NDJSON,
            OtlpExporterFormatEnum::Grpc => Protocols::GRPC,
            OtlpExporterFormatEnum::Protobuf => Protocols::HTTP_PROTOBUF,
        };
    }

    private static function formatEndPoint(string $endpoint, string $protocol): string
    {
        if (Protocols::GRPC === $protocol) {
            return $endpoint.OtlpUtil::method(Signals::TRACE);
        }

        return HttpEndpointResolver::create()->resolveToString($endpoint, Signals::TRACE);
    }

    /**
     * @param array<string, string> $headers
     *
     * @return array<string, string>
     */
    private static function getHeaders(array $headers): array
    {
        return $headers + OtlpUtil::getUserAgentHeader();
    }

    private static function getCompression(OtlpExporterCompressionEnum $compression): string
    {
        return match ($compression) {
            OtlpExporterCompressionEnum::Gzip => KnownValues::VALUE_GZIP,
            OtlpExporterCompressionEnum::None => KnownValues::VALUE_NONE,
        };
    }
}
