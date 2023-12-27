<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Unit\OpenTelemetry\Log\LogProcessor;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LogProcessor\MultiLogProcessorFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LogProcessor\NoopLogProcessorFactory;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LogProcessor\MultiLogProcessorFactory
 */
class MultiLogProcessorFactoryTest extends TestCase
{
    public function testCreateProcessor(): void
    {
        MultiLogProcessorFactory::createProcessor([NoopLogProcessorFactory::createProcessor()]);

        self::expectExceptionObject(new \InvalidArgumentException('Processors should not be empty'));

        MultiLogProcessorFactory::createProcessor();
    }
}
