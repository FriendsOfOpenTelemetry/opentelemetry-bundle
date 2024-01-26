<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Unit\OpenTelemetry\Log\LogProcessor;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\EmptyExporterOptions;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterDsn;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LogExporter\NoopLogExporterFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LogProcessor\SimpleLogProcessorFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Transport\TransportFactory;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LogProcessor\SimpleLogProcessorFactory
 */
class SimpleLogProcessorFactoryTest extends TestCase
{
    public function testCreateProcessor(): void
    {
        SimpleLogProcessorFactory::createProcessor(
            [],
            (new NoopLogExporterFactory(new TransportFactory([])))
                ->createExporter(
                    ExporterDsn::fromString('null://default'),
                    EmptyExporterOptions::fromConfiguration([]),
                ),
        );

        self::expectExceptionObject(new \InvalidArgumentException('Exporter is null'));

        SimpleLogProcessorFactory::createProcessor();
    }
}
