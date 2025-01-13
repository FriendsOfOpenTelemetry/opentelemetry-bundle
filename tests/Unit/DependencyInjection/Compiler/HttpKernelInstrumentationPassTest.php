<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Unit\DependencyInjection\Compiler;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\Compiler\HttpKernelInstrumentationPass;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

#[CoversClass(HttpKernelInstrumentationPass::class)]
class HttpKernelInstrumentationPassTest extends AbstractCompilerPassTestCase
{
    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new HttpKernelInstrumentationPass());
        $container
            ->setDefinition('open_telemetry.instrumentation.http_kernel.trace.event_subscriber', new Definition())
            ->addTag('kernel.event_subscriber');
    }

    public function testRemoveInstrumentationByDefault(): void
    {
        $this->compile();

        self::assertContainerBuilderNotHasService('open_telemetry.instrumentation.http_kernel.trace.event_subscriber');
    }

    public function testDoesNotRemoveInstrumentation(): void
    {
        $this->setParameter('open_telemetry.instrumentation.http_kernel.tracing.enabled', true);

        $this->compile();

        self::assertContainerBuilderHasServiceDefinitionWithTag(
            'open_telemetry.instrumentation.http_kernel.trace.event_subscriber',
            'kernel.event_subscriber',
        );
    }
}
