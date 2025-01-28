<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Unit\OpenTelemetry\Log\LogProcessor;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\EmptyExporterOptions;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterDsn;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LogExporter\NoopLogExporterFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LogProcessor\SimpleLogProcessorFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Transport\TransportFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SimpleLogProcessorFactory::class)]
class SimpleLogProcessorFactoryTest extends TestCase
{
    public function testCreateProcessor(): void
    {
        (new SimpleLogProcessorFactory())->createProcessor(
            [],
            null,
            (new NoopLogExporterFactory(new TransportFactory([])))
                ->createExporter(
                    ExporterDsn::fromString('null://default'),
                    EmptyExporterOptions::fromConfiguration([]),
                ),
        );

        self::expectExceptionObject(new \InvalidArgumentException('You must provide an exporter when using a simple log processor'));

        (new SimpleLogProcessorFactory())->createProcessor();
    }
}
