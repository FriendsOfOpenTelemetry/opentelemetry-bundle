<?php

namespace GaelReyrol\OpenTelemetryBundle\Factory;

use GaelReyrol\OpenTelemetryBundle\DependencyInjection\OtlpExporterCompressionEnum;
use GaelReyrol\OpenTelemetryBundle\DependencyInjection\OtlpExporterFormatEnum;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;

interface SpanExporterFactoryInterface
{
    /**
     * @param array{
     *     endpoint: string,
     *     headers: array<string, string>,
     *     format: ?OtlpExporterFormatEnum,
     *     compression: ?OtlpExporterCompressionEnum,
     * } $options
     */
    public function createFromOptions(array $options): SpanExporterInterface;
}
