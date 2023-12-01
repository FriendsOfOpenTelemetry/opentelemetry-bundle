<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LogExporter;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\OtlpExporterCompressionEnum;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\OtlpExporterFormatEnum;
use OpenTelemetry\Contrib\Otlp\ContentTypes;
use OpenTelemetry\SDK\Common\Export\Stream\StreamTransportFactory;
use OpenTelemetry\SDK\Logs\Exporter\ConsoleExporter;
use OpenTelemetry\SDK\Logs\LogRecordExporterInterface;

final class ConsoleLogExporterFactory implements LogExporterFactoryInterface
{
    public static function create(
        string $endpoint = null,
        array $headers = [],
        OtlpExporterFormatEnum $format = null,
        OtlpExporterCompressionEnum $compression = null,
    ): LogRecordExporterInterface {
        $transport = (new StreamTransportFactory())->create(STDOUT, self::getContentType($format ?? OtlpExporterFormatEnum::Json));

        return new ConsoleExporter($transport);
    }

    private static function getContentType(OtlpExporterFormatEnum $format): string
    {
        return match ($format) {
            OtlpExporterFormatEnum::Json, OtlpExporterFormatEnum::Grpc => ContentTypes::JSON,
            OtlpExporterFormatEnum::Ndjson => ContentTypes::NDJSON,
            OtlpExporterFormatEnum::Protobuf => ContentTypes::PROTOBUF,
        };
    }
}
