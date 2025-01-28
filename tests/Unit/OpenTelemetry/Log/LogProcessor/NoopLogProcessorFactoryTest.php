<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Unit\OpenTelemetry\Log\LogProcessor;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LogProcessor\NoopLogProcessorFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(NoopLogProcessorFactory::class)]
class NoopLogProcessorFactoryTest extends TestCase
{
    public function testCreateProcessor(): void
    {
        self::expectNotToPerformAssertions();

        (new NoopLogProcessorFactory())->createProcessor();
    }
}
