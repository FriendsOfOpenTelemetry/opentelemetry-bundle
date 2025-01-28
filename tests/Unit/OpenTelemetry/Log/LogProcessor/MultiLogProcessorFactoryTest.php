<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Unit\OpenTelemetry\Log\LogProcessor;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LogProcessor\MultiLogProcessorFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LogProcessor\NoopLogProcessorFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MultiLogProcessorFactory::class)]
class MultiLogProcessorFactoryTest extends TestCase
{
    public function testCreateProcessor(): void
    {
        (new MultiLogProcessorFactory())->createProcessor([(new NoopLogProcessorFactory())->createProcessor()]);

        self::expectExceptionObject(new \InvalidArgumentException('You must provide at least one processor when using a multi log processor'));

        (new MultiLogProcessorFactory())->createProcessor();
    }
}
