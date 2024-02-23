<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Unit\DependencyInjection\Compiler;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\Compiler\RemoveMailerInstrumentationPass;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

#[CoversClass(RemoveMailerInstrumentationPass::class)]
class RemoveMailerInstrumentationPassTest extends AbstractCompilerPassTestCase
{
    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new RemoveMailerInstrumentationPass());
        $container->setDefinition('open_telemetry.instrumentation.mailer.trace.event_subscriber', new Definition());
        $container->setDefinition('open_telemetry.instrumentation.mailer.trace.transports', new Definition());
        $container->setDefinition('open_telemetry.instrumentation.mailer.trace.default_transport', new Definition());
        $container->setDefinition('open_telemetry.instrumentation.mailer.trace.mailer', new Definition());
    }

    public function testRemoveInstrumentationByDefault(): void
    {
        $this->compile();

        self::assertContainerBuilderNotHasService('open_telemetry.instrumentation.mailer.trace.event_subscriber');
        self::assertContainerBuilderNotHasService('open_telemetry.instrumentation.mailer.trace.transports');
        self::assertContainerBuilderNotHasService('open_telemetry.instrumentation.mailer.trace.default_transport');
        self::assertContainerBuilderNotHasService('open_telemetry.instrumentation.mailer.trace.mailer');
    }

    public function testDoesNotRemoveInstrumentation(): void
    {
        $this->setParameter('open_telemetry.instrumentation.mailer.tracing.enabled', true);

        $this->compile();

        self::assertContainerBuilderHasService('open_telemetry.instrumentation.mailer.trace.event_subscriber');
        self::assertContainerBuilderHasService('open_telemetry.instrumentation.mailer.trace.transports');
        self::assertContainerBuilderHasService('open_telemetry.instrumentation.mailer.trace.default_transport');
        self::assertContainerBuilderHasService('open_telemetry.instrumentation.mailer.trace.mailer');
    }
}
