<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Unit\DependencyInjection\Compiler;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\Compiler\SetHttpKernelTracingExcludePathsPass;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

#[CoversClass(SetHttpKernelTracingExcludePathsPass::class)]
class SetHttpKernelTracingExcludePathsPassTest extends AbstractCompilerPassTestCase
{
    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new SetHttpKernelTracingExcludePathsPass());

        $container->setDefinition('open_telemetry.instrumentation.http_kernel.trace.event_subscriber', new Definition());
    }

    public function testNoExcludePaths(): void
    {
        $this->compile();

        $httpKernel = $this->container->getDefinition('open_telemetry.instrumentation.http_kernel.trace.event_subscriber');
        self::assertEquals([], $httpKernel->getMethodCalls());
    }

    public function testExcludePaths(): void
    {
        $this->container->setParameter('open_telemetry.instrumentation.http_kernel.tracing.exclude_paths', ['/test']);

        $this->compile();

        $httpKernel = $this->container->getDefinition('open_telemetry.instrumentation.http_kernel.trace.event_subscriber');
        self::assertEquals([['setExcludePaths', [['/test']]]], $httpKernel->getMethodCalls());
    }
}
