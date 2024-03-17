<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Unit\DependencyInjection\Compiler;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\Compiler\HttpKernelTracerLocatorPass;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

#[CoversClass(HttpKernelTracerLocatorPass::class)]
class HttpKernelTracerLocatorPassTest extends AbstractCompilerPassTestCase
{
    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new HttpKernelTracerLocatorPass());

        $container->setDefinition('open_telemetry.instrumentation.http_kernel.trace.event_subscriber', new Definition());
    }

    public function testNoTracerLocator(): void
    {
        $this->compile();

        $httpKernel = $this->container->getDefinition('open_telemetry.instrumentation.http_kernel.trace.event_subscriber');
        self::assertEquals([], $httpKernel->getArguments());
    }

    public function testTracerLocator(): void
    {
        $this->container->setDefinition('tracer1', new Definition())->addTag('open_telemetry.tracer');
        $this->container->setDefinition('tracer2', new Definition())->addTag('open_telemetry.tracer');

        $this->compile();

        $httpKernel = $this->container->getDefinition('open_telemetry.instrumentation.http_kernel.trace.event_subscriber');
        self::assertArrayHasKey('$tracerLocator', $httpKernel->getArguments());
    }
}
