<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Unit\OpenTelemetry\Trace\SpanProcessor;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\SpanProcessor\MultiSpanProcessorFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\SpanProcessor\NoopSpanProcessorFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MultiSpanProcessorFactory::class)]
class MultiSpanProcessorFactoryTest extends TestCase
{
    public function testCreateProcessor(): void
    {
        (new MultiSpanProcessorFactory())->createProcessor([(new NoopSpanProcessorFactory())->createProcessor()]);

        self::expectExceptionObject(new \InvalidArgumentException('Processors should not be empty'));

        (new MultiSpanProcessorFactory())->createProcessor();
    }
}
