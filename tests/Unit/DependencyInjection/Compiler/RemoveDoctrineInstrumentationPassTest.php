<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Unit\DependencyInjection\Compiler;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\Compiler\RemoveDoctrineInstrumentationPass;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

#[CoversClass(RemoveDoctrineInstrumentationPass::class)]
class RemoveDoctrineInstrumentationPassTest extends AbstractCompilerPassTestCase
{
    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new RemoveDoctrineInstrumentationPass());
        $container->setDefinition('open_telemetry.instrumentation.doctrine.trace.middleware', new Definition());
        $container->setDefinition('open_telemetry.instrumentation.doctrine.trace.event_subscriber', new Definition());
    }

    public function testRemoveInstrumentationByDefault(): void
    {
        $this->compile();

        self::assertContainerBuilderNotHasService('open_telemetry.instrumentation.doctrine.trace.middleware');
        self::assertContainerBuilderNotHasService('open_telemetry.instrumentation.doctrine.trace.event_subscriber');
    }

    public function testDoesNotRemoveInstrumentation(): void
    {
        $this->setParameter('open_telemetry.instrumentation.doctrine.tracing.enabled', true);

        $this->compile();

        self::assertContainerBuilderHasService('open_telemetry.instrumentation.doctrine.trace.middleware');
        self::assertContainerBuilderHasService('open_telemetry.instrumentation.doctrine.trace.event_subscriber');
    }
}
