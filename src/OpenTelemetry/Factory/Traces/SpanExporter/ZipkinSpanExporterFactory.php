<?php

namespace GaelReyrol\OpenTelemetryBundle\OpenTelemetry\Factory\Traces\SpanExporter;

use GaelReyrol\OpenTelemetryBundle\OpenTelemetry\OtlpExporterCompressionEnum;
use GaelReyrol\OpenTelemetryBundle\OpenTelemetry\OtlpExporterFormatEnum;
use OpenTelemetry\Contrib\Zipkin\Exporter;
use OpenTelemetry\SDK\Common\Export\Http\PsrTransportFactory;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;

final readonly class ZipkinSpanExporterFactory implements SpanExporterFactoryInterface
{
    public static function create(
        string $endpoint,
        array $headers,
        OtlpExporterFormatEnum $format = null,
        OtlpExporterCompressionEnum $compression = null,
    ): SpanExporterInterface {
        $transport = PsrTransportFactory::discover()->create($endpoint, 'application/json');

        return new Exporter($transport);
    }
}
