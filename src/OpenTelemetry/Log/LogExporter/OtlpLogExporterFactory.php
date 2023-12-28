<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LogExporter;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterDsn;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterOptionsInterface;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LogExporterEndpoint;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Transport\TransportEnum;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Transport\TransportFactoryInterface;
use OpenTelemetry\Contrib\Otlp\LogsExporter;

final class OtlpLogExporterFactory implements LogExporterFactoryInterface
{
    public static function createExporter(ExporterDsn $dsn, ExporterOptionsInterface $options): LogsExporter
    {
        $exporter = LogExporterEnum::fromDsn($dsn);
        if (LogExporterEnum::Otlp !== $exporter) {
            throw new \InvalidArgumentException('DSN exporter must be of type Otlp.');
        }

        $transport = TransportEnum::fromDsn($dsn);
        if (null === $transport) {
            throw new \InvalidArgumentException('Could not find a transport from DSN for this exporter factory.');
        }

        /** @var TransportFactoryInterface $transportFactory */
        $transportFactory = call_user_func(
            [$transport->getFactoryClass(), 'fromExporter'],
            LogExporterEndpoint::fromDsn($dsn),
            $options,
        );

        return new LogsExporter($transportFactory->createTransport());
    }
}
