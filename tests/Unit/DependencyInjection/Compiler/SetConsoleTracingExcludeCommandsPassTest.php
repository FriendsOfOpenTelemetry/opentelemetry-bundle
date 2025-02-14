<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Unit\DependencyInjection\Compiler;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\Compiler\SetConsoleTracingExcludeCommandsPass;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

#[CoversClass(SetConsoleTracingExcludeCommandsPass::class)]
class SetConsoleTracingExcludeCommandsPassTest extends AbstractCompilerPassTestCase
{
    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new SetConsoleTracingExcludeCommandsPass());

        $container->setDefinition('open_telemetry.instrumentation.console.trace.event_subscriber', new Definition());
    }

    public function testNoExcludeCommands(): void
    {
        $this->compile();

        $httpKernel = $this->container->getDefinition('open_telemetry.instrumentation.console.trace.event_subscriber');
        self::assertEquals([], $httpKernel->getMethodCalls());
    }

    public function testExcludeCommands(): void
    {
        $this->container->setParameter('open_telemetry.instrumentation.console.tracing.exclude_commands', ['dummy']);

        $this->compile();

        $httpKernel = $this->container->getDefinition('open_telemetry.instrumentation.console.trace.event_subscriber');
        self::assertEquals([['setExcludeCommands', [['dummy']]]], $httpKernel->getMethodCalls());
    }
}
