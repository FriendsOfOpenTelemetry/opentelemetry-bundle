<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Unit\OpenTelemetry\Metric\MeterProvider;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MeterProvider\NoopMeterProviderFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(NoopMeterProviderFactory::class)]
class NoopMeterProviderFactoryTest extends TestCase
{
    public function testCreateProvider(): void
    {
        self::expectNotToPerformAssertions();

        (new NoopMeterProviderFactory())->createProvider();
    }
}
