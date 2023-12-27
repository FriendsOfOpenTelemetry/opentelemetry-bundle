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
    public static function createExporter(ExporterDsn $dsn, ExporterOptionsInterface $options): MetricExporter
    {
        assert($options instanceof MetricExporterOptions);

        $exporter = MetricExporterEnum::fromDsn($dsn);
        if (MetricExporterEnum::Otlp !== $exporter) {
            throw new \InvalidArgumentException('DSN exporter must be of type Otlp.');
        }

        $transport = TransportEnum::fromDsn($dsn);
        if (null === $transport) {
            throw new \InvalidArgumentException('Could not find a transport from DSN for this exporter factory.');
        }

        /** @var TransportFactoryInterface $transportFactory */
        $transportFactory = call_user_func(
            [$transport->getFactoryClass(), 'fromExporter'],
            MetricExporterEndpoint::fromDsn($dsn),
            $options->getOtlpOptions(),
        );

        return new MetricExporter($transportFactory->createTransport(), $options->getTemporality()->toData());
    }
}
