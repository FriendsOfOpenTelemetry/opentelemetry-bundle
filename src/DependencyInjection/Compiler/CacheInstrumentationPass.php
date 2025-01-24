<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class CacheInstrumentationPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->getParameter('open_telemetry.instrumentation.cache.tracing.enabled')) {
            return;
        }

        foreach ($container->findTaggedServiceIds('cache.pool') as $serviceId => $tags) {
            $cachePoolDefinition = $container->getDefinition($serviceId);
            if ($cachePoolDefinition->isAbstract()) {
                continue;
            }

            $definitionClass = $this->resolveDefinitionClass($container, $cachePoolDefinition);
            if (null === $definitionClass) {
                continue;
            }

            $traceableCachePoolDefinition = new ChildDefinition('open_telemetry.instrumentation.cache.trace.adapter');
            $traceableCachePoolDefinition
                ->setDecoratedService($serviceId)
                ->setArgument('$adapter', new Reference('.inner'));

            $container->setDefinition($serviceId.'.tracer', $traceableCachePoolDefinition);
        }

        foreach ($container->findTaggedServiceIds('cache.taggable') as $serviceId => $tags) {
            $cachePoolDefinition = $container->getDefinition($serviceId);
            if ($cachePoolDefinition->isAbstract()) {
                continue;
            }

            $definitionClass = $this->resolveDefinitionClass($container, $cachePoolDefinition);
            if (null === $definitionClass) {
                continue;
            }

            $traceableCachePoolDefinition = new ChildDefinition('open_telemetry.instrumentation.cache.trace.tag_aware_adapter');
            $traceableCachePoolDefinition
                ->setDecoratedService($serviceId)
                ->setArgument('$adapter', new Reference('.inner'));

            $container->setDefinition($serviceId.'.tracer', $traceableCachePoolDefinition);
        }
    }

    private function resolveDefinitionClass(ContainerBuilder $container, Definition $definition): ?string
    {
        $class = $definition->getClass();

        while (null === $class && $definition instanceof ChildDefinition) {
            $definition = $container->findDefinition($definition->getParent());
            $class = $definition->getClass();
        }

        return $class;
    }
}
