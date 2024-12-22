<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Unit\OpenTelemetry\Resource;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Resource\ResourceInfoFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ResourceInfoFactory::class)]
class ResourceInfoFactoryTest extends TestCase
{
    public function testCreateResourceInfo(): void
    {
        $resource = ResourceInfoFactory::create('FriendsOfOpenTelemetry/OpenTelemetry', 'Test', '0.0.0', 'test');

        $attributes = $resource->getAttributes();
        self::assertSame('FriendsOfOpenTelemetry/OpenTelemetry', $attributes->get('service.namespace'));
        self::assertSame('Test', $attributes->get('service.name'));
        self::assertSame('0.0.0', $attributes->get('service.version'));
        self::assertSame('test', $attributes->get('deployment.environment.name'));
    }
}
