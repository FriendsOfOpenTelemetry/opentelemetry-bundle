<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Unit\DependencyInjection\Compiler;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\Compiler\DoctrineInstrumentationPass;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

#[CoversClass(DoctrineInstrumentationPass::class)]
class DoctrineInstrumentationPassTest extends AbstractCompilerPassTestCase
{
    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new DoctrineInstrumentationPass());
        $container->setDefinition('open_telemetry.instrumentation.doctrine.trace.middleware', new Definition());
        $container->setDefinition('open_telemetry.instrumentation.doctrine.trace.event_subscriber', new Definition());
    }

    public function testRemoveInstrumentationByDefault(): void
    {
        $this->compile();

        self::assertContainerBuilderNotHasService('open_telemetry.instrumentation.doctrine.trace.middleware');
    }

    public function testDoesNotRemoveInstrumentation(): void
    {
        $this->setParameter('open_telemetry.instrumentation.doctrine.tracing.enabled', true);

        $this->compile();

        self::assertContainerBuilderHasServiceDefinitionWithTag(
            'open_telemetry.instrumentation.doctrine.trace.middleware',
            'doctrine.middleware',
        );
    }
}
