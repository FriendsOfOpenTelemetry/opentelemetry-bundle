<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Unit\OpenTelemetry\Log\LoggerProvider;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LoggerProvider\LoggerProviderFactory;
use OpenTelemetry\SDK\Logs\Processor\NoopLogRecordProcessor;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LoggerProvider\LoggerProviderFactory
 */
class LoggerProviderFactoryTest extends TestCase
{
    public function testCreateProvider(): void
    {
        self::expectNotToPerformAssertions();

        LoggerProviderFactory::createProvider(new NoopLogRecordProcessor());
    }
}
