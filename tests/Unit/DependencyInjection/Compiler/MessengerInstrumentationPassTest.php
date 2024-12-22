<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Unit\DependencyInjection\Compiler;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\Compiler\MessengerInstrumentationPass;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

#[CoversClass(MessengerInstrumentationPass::class)]
class MessengerInstrumentationPassTest extends AbstractCompilerPassTestCase
{
    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new MessengerInstrumentationPass());
        $container->setAlias('messenger.transport.open_telemetry_tracer.factory', 'open_telemetry.instrumentation.messenger.trace.transport_factory');
        $container->setAlias('messenger.middleware.open_telemetry_tracer', 'open_telemetry.instrumentation.messenger.trace.middleware');

        $container->setDefinition('open_telemetry.instrumentation.messenger.trace.event_subscriber', new Definition());
        $container->setDefinition('open_telemetry.instrumentation.messenger.trace.transport', new Definition());
        $container->setDefinition('open_telemetry.instrumentation.messenger.trace.transport_factory', new Definition());
        $container->setDefinition('open_telemetry.instrumentation.messenger.trace.middleware', new Definition());
    }

    public function testRemoveInstrumentationByDefault(): void
    {
        $this->compile();

        self::assertContainerBuilderNotHasService('messenger.transport.open_telemetry_tracer.factory');
        self::assertContainerBuilderNotHasService('messenger.middleware.open_telemetry_tracer');

        self::assertContainerBuilderNotHasService('open_telemetry.instrumentation.messenger.trace.transport');
        self::assertContainerBuilderNotHasService('open_telemetry.instrumentation.messenger.trace.transport_factory');
        self::assertContainerBuilderNotHasService('open_telemetry.instrumentation.messenger.trace.middleware');
    }

    public function testDoesNotRemoveInstrumentation(): void
    {
        $this->setParameter('open_telemetry.instrumentation.messenger.tracing.enabled', true);

        $this->compile();

        self::assertContainerBuilderHasService('messenger.transport.open_telemetry_tracer.factory');
        self::assertContainerBuilderHasService('messenger.middleware.open_telemetry_tracer');

        self::assertContainerBuilderHasService('open_telemetry.instrumentation.messenger.trace.event_subscriber');
        self::assertContainerBuilderHasService('open_telemetry.instrumentation.messenger.trace.transport');
        self::assertContainerBuilderHasServiceDefinitionWithTag(
            'open_telemetry.instrumentation.messenger.trace.transport_factory',
            'messenger.transport_factory'
        );
        self::assertContainerBuilderHasServiceDefinitionWithTag(
            'open_telemetry.instrumentation.messenger.trace.transport_factory',
            'kernel.reset',
            ['method' => 'reset'],
        );
        self::assertContainerBuilderHasService('open_telemetry.instrumentation.messenger.trace.middleware');

        self::assertContainerBuilderHasAlias('messenger.transport.open_telemetry_tracer.factory', 'open_telemetry.instrumentation.messenger.trace.transport_factory');
        self::assertContainerBuilderHasAlias('messenger.middleware.open_telemetry_tracer', 'open_telemetry.instrumentation.messenger.trace.middleware');
    }
}
