<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Unit\DependencyInjection\Compiler;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\Compiler\TwigInstrumentationPass;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

#[CoversClass(TwigInstrumentationPass::class)]
class TwigInstrumentationPassTest extends AbstractCompilerPassTestCase
{
    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new TwigInstrumentationPass());
        $container->setDefinition('open_telemetry.instrumentation.twig.trace.extension', new Definition());
    }

    public function testRemoveInstrumentationByDefault(): void
    {
        $this->compile();

        self::assertEmpty($this->container->getDefinition('open_telemetry.instrumentation.twig.trace.extension')->getTags());
    }

    public function testDoesNotRemoveInstrumentation(): void
    {
        $this->setParameter('open_telemetry.instrumentation.twig.tracing.enabled', true);

        $this->compile();

        self::assertContainerBuilderHasServiceDefinitionWithTag('open_telemetry.instrumentation.twig.trace.extension', 'twig.extension');
    }
}
