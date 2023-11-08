<?php

namespace GaelReyrol\OpenTelemetryBundle\Factory;

use GaelReyrol\OpenTelemetryBundle\DependencyInjection\OtlpExporterCompressionEnum;
use GaelReyrol\OpenTelemetryBundle\DependencyInjection\OtlpExporterFormatEnum;
use OpenTelemetry\API\Signals;
use OpenTelemetry\Contrib\Otlp\HttpEndpointResolver;
use OpenTelemetry\Contrib\Otlp\OtlpUtil;
use OpenTelemetry\Contrib\Otlp\Protocols;
use OpenTelemetry\Contrib\Otlp\SpanExporter;
use OpenTelemetry\SDK\Common\Configuration\KnownValues;
use OpenTelemetry\SDK\Common\Export\TransportFactoryInterface;
use OpenTelemetry\SDK\Registry;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;

final class OtlpSpanExporterFactory implements SpanExporterFactoryInterface
{
    public function __construct(
        private readonly ?TransportFactoryInterface $transportFactory = null
    ) {
    }

    public function createFromOptions(array $options): SpanExporterInterface
    {
        $protocol = $this->getProtocol($options['format']);
        $contentType = Protocols::contentType($protocol);
        $headers = $this->getHeaders($options['headers']);
        $compression = $this->getCompression($options['compression']);

        $factoryClass = Registry::transportFactory($protocol);
        $transport = ($this->transportFactory ?? new $factoryClass())->create(
            $this->formatEndPoint($options['endpoint'], $protocol),
            $contentType,
            $headers,
            $compression,
        );

        return new SpanExporter($transport);
    }

    private function getProtocol(OtlpExporterFormatEnum $format): string
    {
        return match ($format) {
            OtlpExporterFormatEnum::Json => Protocols::HTTP_JSON,
            OtlpExporterFormatEnum::Ndjson => Protocols::HTTP_NDJSON,
            OtlpExporterFormatEnum::Grpc => Protocols::GRPC,
            OtlpExporterFormatEnum::Protobuf => Protocols::HTTP_PROTOBUF,
        };
    }

    private function formatEndPoint(string $endpoint, string $protocol): string
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
    private function getHeaders(array $headers): array
    {
        return $headers + OtlpUtil::getUserAgentHeader();
    }

    private function getCompression(OtlpExporterCompressionEnum $compression): string
    {
        return match ($compression) {
            OtlpExporterCompressionEnum::Gzip => KnownValues::VALUE_GZIP,
            OtlpExporterCompressionEnum::None => KnownValues::VALUE_NONE,
        };
    }
}
