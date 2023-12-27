<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Unit\OpenTelemetry\Log\LoggerProvider;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LoggerProvider\NoopLoggerProviderFactory;
use OpenTelemetry\SDK\Logs\Processor\NoopLogRecordProcessor;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LoggerProvider\NoopLoggerProviderFactory
 */
class NoopLoggerProviderFactoryTest extends TestCase
{
    public function testCreateProvider(): void
    {
        self::expectNotToPerformAssertions();

        NoopLoggerProviderFactory::createProvider(new NoopLogRecordProcessor());
    }
}
