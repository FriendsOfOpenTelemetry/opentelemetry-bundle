<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Unit\Instrumentation\Symfony\Routing;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Symfony\Framework\Routing\TraceableRouteLoader;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Routing\AttributeRouteControllerLoader;

#[CoversClass(TraceableRouteLoader::class)]
class TraceableRouteLoaderTest extends TestCase
{
    private TraceableRouteLoader $loader;

    protected function setUp(?string $env = null): void
    {
        parent::setUp();

        $this->loader = new TraceableRouteLoader(new AttributeRouteControllerLoader($env));
    }

    public function testTraceableAction(): void
    {
        $routes = $this->loader->load(TraceableActionController::class);
        self::assertCount(1, $routes);
        self::assertEquals('/path', $routes->get('action')->getPath());
        self::assertEquals([
            '_traceable' => true,
            '_controller' => 'FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Unit\Instrumentation\Symfony\Routing\TraceableActionController::action',
            '_tracer' => 'test',
        ], $routes->get('action')->getDefaults());
    }

    public function testTraceableClass(): void
    {
        $routes = $this->loader->load(TraceableClassController::class);
        self::assertCount(1, $routes);
        self::assertEquals('/path', $routes->get('action')->getPath());
        self::assertEquals([
            '_traceable' => true,
            '_controller' => 'FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Unit\Instrumentation\Symfony\Routing\TraceableClassController',
            '_tracer' => 'test',
        ], $routes->get('action')->getDefaults());
    }
}
