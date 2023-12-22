<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LogExporter;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterDsn;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterOptionsInterface;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LogExporterEndpoint;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Transport\TransportEnum;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Transport\TransportFactoryInterface;
use OpenTelemetry\SDK\Logs\Exporter\ConsoleExporter;

final class ConsoleLogExporterFactory implements LogExporterFactoryInterface
{
    public static function create(ExporterDsn $dsn, ExporterOptionsInterface $options): ConsoleExporter
    {
        $transportFactoryClass = TransportEnum::from($dsn->getTransport())->getFactoryClass();
        /** @var TransportFactoryInterface $transportFactory */
        $transportFactory = call_user_func([$transportFactoryClass, 'fromExporter'], LogExporterEndpoint::fromDsn($dsn), $options);

        return new ConsoleExporter($transportFactory->create());
    }
}
