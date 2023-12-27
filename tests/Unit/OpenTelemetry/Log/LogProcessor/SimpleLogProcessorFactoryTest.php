<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Unit\OpenTelemetry\Log\LogProcessor;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LogExporter\NoopLogExporterFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LogProcessor\SimpleLogProcessorFactory;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LogProcessor\SimpleLogProcessorFactory
 */
class SimpleLogProcessorFactoryTest extends TestCase
{
    public function testCreateProcessor(): void
    {
        SimpleLogProcessorFactory::createProcessor(exporter: NoopLogExporterFactory::createExporter());

        self::expectExceptionObject(new \InvalidArgumentException('Exporter is null'));

        SimpleLogProcessorFactory::createProcessor();
    }
}
