<?php

namespace GaelReyrol\OpenTelemetryBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('open_telemetry');

        $rootNode = $treeBuilder->getRootNode();

        $this->addServiceSection($rootNode);
        $this->addInstrumentationSection($rootNode);
        $this->addTracesSection($rootNode);
        $this->addLogsSection($rootNode);
        $this->addMetricsSection($rootNode);

        return $treeBuilder;
    }

    private function addServiceSection(ArrayNodeDefinition $node): void
    {
        $node
            ->children()
                ->arrayNode('service')
                ->addDefaultsIfNotSet()
                ->children()
                    ->scalarNode('namespace')
                        ->cannotBeEmpty()
                        ->isRequired()
                        ->example('MyOrganization')
                    ->end()
                    ->scalarNode('name')
                        ->cannotBeEmpty()
                        ->isRequired()
                        ->example('MyApp')
                    ->end()
                    ->scalarNode('version')
                        ->cannotBeEmpty()
                        ->isRequired()
                        ->example('1.0.0')
                    ->end()
                    ->scalarNode('environment')
                        ->defaultValue('%kernel.environment%')
                        ->cannotBeEmpty()
                        ->isRequired()
                    ->end()
                ->end()
            ->end()
        ;
    }

    private function addInstrumentationSection(ArrayNodeDefinition $node): void
    {
        $node
            ->children()
                ->arrayNode('instrumentation')
                ->addDefaultsIfNotSet()
                ->children()
                    ->arrayNode('http_kernel')
                        ->canBeDisabled()
                        ->children()
                            ->scalarNode('provider')
                                ->defaultValue('default_provider')
                                ->isRequired()
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('console')
                        ->canBeDisabled()
                        ->children()
                            ->scalarNode('provider')
                                ->defaultValue('default_provider')
                                ->isRequired()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    private function addTracesSection(ArrayNodeDefinition $node): void
    {
        $node
            ->children()
            ->arrayNode('traces')
            ->addDefaultsIfNotSet()
            ->canBeDisabled()
            ->children()
                ->append($this->getTracingProvidersNode())
                ->append($this->getTracingProcessorsNode())
                ->append($this->getTracingExportersNode())
            ->end()
        ;
    }

    private function getTracingProvidersNode(): ArrayNodeDefinition
    {
        $treeBuilder = new TreeBuilder('providers');

        $node = $treeBuilder->getRootNode()
            ->requiresAtLeastOneElement()
            ->useAttributeAsKey('provider')
            ->arrayPrototype()
                ->children()
                    ->enumNode('type')
                        ->defaultValue(OpenTelemetryProviderEnum::Default->value)
                        ->values(array_map(fn (OpenTelemetryProviderEnum $enum) => $enum->value, OpenTelemetryProviderEnum::cases()))
                        ->isRequired()
                    ->end()
                    ->arrayNode('sampler')
                        ->beforeNormalization()
                            ->ifString()
                            ->then(static fn ($v) => ['type' => $v])
                        ->end()
                        ->children()
                            ->enumNode('type')
                                ->defaultValue(OpenTelemetrySamplerEnum::AlwaysOn->value)
                                ->values(array_map(fn (OpenTelemetrySamplerEnum $enum) => $enum->value, OpenTelemetrySamplerEnum::cases()))
                                ->isRequired()
                            ->end()
                        ->end()
                    ->end()
                    ->scalarNode('processor')->isRequired()->end()
                ->end()
            ->end();

        return $node;
    }

    private function getTracingProcessorsNode(): ArrayNodeDefinition
    {
        $treeBuilder = new TreeBuilder('processors');

        $node = $treeBuilder->getRootNode()
            ->requiresAtLeastOneElement()
            ->useAttributeAsKey('processor')
            ->arrayPrototype()
                ->children()
                    ->enumNode('type')
                        ->defaultValue('simple')
                        ->values(array_map(fn (OpenTelemetryProcessorEnum $enum) => $enum->value, OpenTelemetryProcessorEnum::cases()))
                        ->isRequired()
                    ->end()
                    ->arrayNode('processors')
                        ->info('Required if processor type is multi')
                    ->end()
                    ->scalarNode('exporter')
                        ->info('Required if processor type is simple or batch')
                    ->end()
                ->end()
            ->end()
        ;

        return $node;
    }

    private function getTracingExportersNode(): ArrayNodeDefinition
    {
        $treeBuilder = new TreeBuilder('exporters');

        $node = $treeBuilder->getRootNode()
            ->requiresAtLeastOneElement()
            ->useAttributeAsKey('exporter')
            ->arrayPrototype()
                ->children()
                    ->enumNode('type')
                        ->defaultValue(OpenTelemetryExporterEnum::Otlp->value)
                        ->values(array_map(fn (OpenTelemetryExporterEnum $enum) => $enum->value, OpenTelemetryExporterEnum::cases()))
                        ->isRequired()
                    ->end()
                    ->scalarNode('dsn')
                        ->cannotBeEmpty()
                        ->isRequired()
                    ->end()
                ->end()
            ->end()
        ;

        return $node;
    }

    private function addLogsSection(ArrayNodeDefinition $node): void
    {
        $node
            ->children()
            ->arrayNode('logs')
            ->addDefaultsIfNotSet()
            ->canBeDisabled()
            ->end()
        ;
    }

    private function addMetricsSection(ArrayNodeDefinition $node): void
    {
        $node
            ->children()
            ->arrayNode('metrics')
            ->addDefaultsIfNotSet()
            ->canBeDisabled()
            ->end()
        ;
    }
}
