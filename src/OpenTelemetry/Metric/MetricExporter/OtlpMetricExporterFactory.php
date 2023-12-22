<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MetricExporter;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterDsn;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterOptionsInterface;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MetricExporterEndpoint;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MetricExporterOptions;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Transport\TransportEnum;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Transport\TransportFactoryInterface;
use OpenTelemetry\Contrib\Otlp\MetricExporter;

final class OtlpMetricExporterFactory implements MetricExporterFactoryInterface
{
    public static function create(ExporterDsn $dsn, ExporterOptionsInterface $options): MetricExporter
    {
        assert($options instanceof MetricExporterOptions);

        $transportFactoryClass = TransportEnum::from($dsn->getTransport())->getFactoryClass();
        /** @var TransportFactoryInterface $transportFactory */
        $transportFactory = call_user_func(
            [$transportFactoryClass, 'fromExporter'],
            MetricExporterEndpoint::fromDsn($dsn),
            $options->getOtlpOptions(),
        );

        return new MetricExporter($transportFactory->create(), $options->getTemporality()->toData());
    }
}
