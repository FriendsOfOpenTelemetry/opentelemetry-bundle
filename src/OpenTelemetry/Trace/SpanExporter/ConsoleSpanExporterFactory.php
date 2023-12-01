<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\SpanExporter;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\OtlpExporterCompressionEnum;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\OtlpExporterFormatEnum;
use OpenTelemetry\SDK\Registry;
use OpenTelemetry\SDK\Trace\SpanExporter\ConsoleSpanExporter;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;

final readonly class ConsoleSpanExporterFactory implements SpanExporterFactoryInterface
{
    public static function create(
        string $endpoint = null,
        array $headers = null,
        OtlpExporterFormatEnum $format = null,
        OtlpExporterCompressionEnum $compression = null,
    ): SpanExporterInterface {
        $transport = Registry::transportFactory('stream')
            ->create('php://stdout', 'application/json');

        return new ConsoleSpanExporter($transport);
    }
}
