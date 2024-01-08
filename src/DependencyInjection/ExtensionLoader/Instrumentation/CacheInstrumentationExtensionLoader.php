<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\ExtensionLoader\Instrumentation;

use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Definition;

final class CacheInstrumentationExtensionLoader extends AbstractInstrumentationExtensionLoader
{
    public function __construct()
    {
        parent::__construct('cache');
    }

    protected function assertInstrumentationCanHappen(): void
    {
    }

    protected function setTracingDefinitions(): void
    {
        foreach ($this->container->findTaggedServiceIds('cache.pool') as $serviceId => $tags) {
            $cachePoolDefinition = $this->container->getDefinition($serviceId);

            var_dump($serviceId);

            if ($cachePoolDefinition->isAbstract()) {
                continue;
            }

            $definitionClass = $this->resolveDefinitionClass($cachePoolDefinition);

            if (null === $definitionClass) {
                continue;
            }

            if (is_subclass_of($definitionClass, TagAwareAdapterInterface::class)) {
                $traceableCachePoolDefinition = new ChildDefinition('open_telemetry.instrumentation.cache.trace.tag_aware_adapter');
            } else {
                $traceableCachePoolDefinition = new ChildDefinition('open_telemetry.instrumentation.cache.trace.adapter');
            }

            $traceableCachePoolDefinition->setDecoratedService($serviceId);
            $traceableCachePoolDefinition->setArgument('$tracer', $this->getInstrumentationTracerOrDefaultTracer());

            $this->container->setDefinition($serviceId.'.tracer', $traceableCachePoolDefinition);
        }
    }

    protected function setMeteringDefinitions(): void
    {
        return;
    }

    private function resolveDefinitionClass(Definition $definition): ?string
    {
        $class = $definition->getClass();

        while (null === $class && $definition instanceof ChildDefinition) {
            $definition = $this->container->findDefinition($definition->getParent());
            $class = $definition->getClass();
        }

        return $class;
    }
}
