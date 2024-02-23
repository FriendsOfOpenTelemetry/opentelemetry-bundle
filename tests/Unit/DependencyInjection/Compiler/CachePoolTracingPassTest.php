<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Unit\DependencyInjection\Compiler;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\Compiler\CachePoolTracingPass;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

#[CoversClass(CachePoolTracingPass::class)]
class CachePoolTracingPassTest extends AbstractCompilerPassTestCase
{
    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new CachePoolTracingPass());

        $container->setDefinition('open_telemetry.instrumentation.cache.trace.tag_aware_adapter', new Definition());
        $container->setDefinition('open_telemetry.instrumentation.cache.trace.adapter', new Definition());

        $this->registerService('cache.test', ArrayAdapter::class)
            ->setPublic(true)
            ->addTag('cache.pool');

        $this->registerService('cache.test.taggable', TagAwareAdapter::class)
            ->setPublic(true)
            ->setArguments([new Reference('cache.app')])
            ->addTag('cache.pool', ['pool' => 'cache.app']);
    }

    public function testRemoveInstrumentationByDefault(): void
    {
        $this->compile();

        self::assertContainerBuilderNotHasService('open_telemetry.instrumentation.cache.trace.tag_aware_adapter');
        self::assertContainerBuilderNotHasService('open_telemetry.instrumentation.cache.trace.adapter');

        self::assertContainerBuilderNotHasService('cache.test.tracer');
        self::assertContainerBuilderNotHasService('cache.test.taggable.tracer');
    }

    public function testDoesNotRemoveInstrumentation(): void
    {
        $this->setParameter('open_telemetry.instrumentation.cache.tracing.enabled', true);

        $this->compile();

        self::assertContainerBuilderHasService('open_telemetry.instrumentation.cache.trace.tag_aware_adapter');
        self::assertContainerBuilderHasService('open_telemetry.instrumentation.cache.trace.adapter');

        self::assertContainerBuilderHasService('cache.test');
        self::assertContainerBuilderHasServiceDefinitionWithArgument('cache.test.tracer', '$adapter', new Reference('.inner'));

        self::assertContainerBuilderHasService('cache.test.taggable');
        self::assertContainerBuilderHasServiceDefinitionWithArgument('cache.test.taggable.tracer', '$adapter', new Reference('.inner'));
    }
}
