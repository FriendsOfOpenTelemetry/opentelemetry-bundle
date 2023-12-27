<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\SpanExporter;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterDsn;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterOptionsInterface;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\TraceExporterEndpoint;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Transport\TransportEnum;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Transport\TransportFactoryInterface;
use OpenTelemetry\Contrib\Otlp\SpanExporter;

final readonly class OtlpSpanExporterFactory implements SpanExporterFactoryInterface
{
    public static function createExporter(ExporterDsn $dsn, ExporterOptionsInterface $options): SpanExporter
    {
        $exporter = TraceExporterEnum::fromDsn($dsn);
        if (TraceExporterEnum::Otlp !== $exporter) {
            throw new \InvalidArgumentException('DSN exporter must be of type Otlp.');
        }

        $transportFactoryClass = TransportEnum::from($dsn->getTransport())->getFactoryClass();
        /** @var TransportFactoryInterface $transportFactory */
        $transportFactory = call_user_func(
            [$transportFactoryClass, 'fromExporter'],
            TraceExporterEndpoint::fromDsn($dsn),
            $options,
        );

        return new SpanExporter($transportFactory->createTransport());
    }
}
