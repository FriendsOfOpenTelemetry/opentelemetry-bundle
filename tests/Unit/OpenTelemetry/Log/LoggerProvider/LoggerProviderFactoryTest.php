<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Unit\OpenTelemetry\Log\LoggerProvider;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LoggerProvider\DefaultLoggerProviderFactory;
use OpenTelemetry\SDK\Logs\Processor\NoopLogRecordProcessor;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(DefaultLoggerProviderFactory::class)]
class LoggerProviderFactoryTest extends TestCase
{
    public function testCreateProvider(): void
    {
        self::expectNotToPerformAssertions();

        (new DefaultLoggerProviderFactory())->createProvider(new NoopLogRecordProcessor());
    }
}
