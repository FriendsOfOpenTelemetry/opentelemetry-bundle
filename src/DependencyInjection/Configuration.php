<?php

declare(strict_types=1);

namespace DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('opentelemetry');

        /** @phpstan-ignore-next-line */
        $treeBuilder->getRootNode()
            ->canBeEnabled()
            ->children()
            ->end()
        ;

        return $treeBuilder;
    }
}
