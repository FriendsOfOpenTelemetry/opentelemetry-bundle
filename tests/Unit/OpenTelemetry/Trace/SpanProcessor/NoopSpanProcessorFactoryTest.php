<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Unit\OpenTelemetry\Trace\SpanProcessor;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\SpanProcessor\NoopSpanProcessorFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(NoopSpanProcessorFactory::class)]
class NoopSpanProcessorFactoryTest extends TestCase
{
    public function testCreateProcessor(): void
    {
        self::expectNotToPerformAssertions();

        (new NoopSpanProcessorFactory())->createProcessor();
    }
}
