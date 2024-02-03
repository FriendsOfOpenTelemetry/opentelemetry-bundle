<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Unit\DependencyInjection\Compiler;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\Compiler\RemoveConsoleInstrumentationPass;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

#[CoversClass(RemoveConsoleInstrumentationPass::class)]
class RemoveConsoleInstrumentationPassTest extends AbstractCompilerPassTestCase
{
    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new RemoveConsoleInstrumentationPass());
        $container->setDefinition('open_telemetry.instrumentation.console.trace.event_subscriber', new Definition());
    }

    public function testRemoveInstrumentationByDefault(): void
    {
        $this->compile();

        self::assertContainerBuilderNotHasService('open_telemetry.instrumentation.console.trace.event_subscriber');
    }

    public function testDoesNotRemoveInstrumentation(): void
    {
        $this->setParameter('open_telemetry.instrumentation.console.tracing.enabled', true);

        $this->compile();

        self::assertContainerBuilderHasService('open_telemetry.instrumentation.console.trace.event_subscriber');
    }
}
