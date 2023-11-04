<?php

namespace GaelReyrol\OpenTelemetryBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('open_telemetry');

        /** @phpstan-ignore-next-line */
        $treeBuilder->getRootNode()
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('tracing')
                    ->addDefaultsIfNotSet()
                    ->canBeDisabled()
                ->end()
                ->arrayNode('logging')
                    ->addDefaultsIfNotSet()
                    ->canBeDisabled()
                ->end()
                ->arrayNode('metering')
                    ->addDefaultsIfNotSet()
                    ->canBeDisabled()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
