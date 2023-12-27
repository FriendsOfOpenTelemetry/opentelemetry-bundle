<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Unit\OpenTelemetry\Metric\MeterProvider;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MeterProvider\MeterProviderFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MetricExporter\NoopMetricExporterFactory;
use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilter\NoneExemplarFilter;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MeterProvider\MeterProviderFactory
 */
class MeterProviderFactoryTest extends TestCase
{
    public function testCreateProvider(): void
    {
        self::expectNotToPerformAssertions();

        MeterProviderFactory::createProvider(
            NoopMetricExporterFactory::createExporter(),
            new NoneExemplarFilter(),
        );
    }
}
