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
        (new MultiLogProcessorFactory())->createProcessor([(new NoopLogProcessorFactory())->createProcessor()]);

        self::expectExceptionObject(new \InvalidArgumentException('You must provide at least one processor when using a multi log processor'));

        (new MultiLogProcessorFactory())->createProcessor();
    }
}
