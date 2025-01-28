<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Unit\OpenTelemetry\Log\LoggerProvider;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LoggerProvider\NoopLoggerProviderFactory;
use OpenTelemetry\SDK\Logs\Processor\NoopLogRecordProcessor;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(NoopLoggerProviderFactory::class)]
class NoopLoggerProviderFactoryTest extends TestCase
{
    public function testCreateProvider(): void
    {
        self::expectNotToPerformAssertions();

        (new NoopLoggerProviderFactory())->createProvider(new NoopLogRecordProcessor());
    }
}
