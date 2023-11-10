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
                            ->scalarNode('tracing_provider')
                                ->info('The tracing provider to use, defaults to `default_provider`')
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('console')
                        ->canBeDisabled()
                        ->children()
                            ->scalarNode('tracing_provider')
                                ->info('The tracing provider to use, defaults to `default_provider`')
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
                ->scalarNode('default_provider')
                    ->info('The default provider to use among the `providers`')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
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
                        ->defaultValue(TraceProviderEnum::Default->value)
                        ->values(array_map(fn (TraceProviderEnum $enum) => $enum->value, TraceProviderEnum::cases()))
                        ->isRequired()
                    ->end()
                    ->arrayNode('sampler')
                        ->beforeNormalization()
                            ->ifString()
                            ->then(static fn ($v) => ['type' => $v])
                        ->end()
                        ->children()
                            ->enumNode('type')
                                ->defaultValue(TraceSamplerEnum::AlwaysOn->value)
                                ->values(array_map(fn (TraceSamplerEnum $enum) => $enum->value, TraceSamplerEnum::cases()))
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
                        ->values(array_map(fn (SpanProcessorEnum $enum) => $enum->value, SpanProcessorEnum::cases()))
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
                        ->defaultValue(TraceExporterEnum::Otlp->value)
                        ->values(array_map(fn (TraceExporterEnum $enum) => $enum->value, TraceExporterEnum::cases()))
                        ->isRequired()
                    ->end()
                    ->scalarNode('endpoint')
                        ->cannotBeEmpty()
                        ->isRequired()
                    ->end()
                    ->enumNode('format')
                        ->info(sprintf('Required if exporter type is %s', TraceExporterEnum::Otlp->value))
                        ->values(array_map(fn (OtlpExporterFormatEnum $enum) => $enum->value, OtlpExporterFormatEnum::cases()))
                    ->end()
                    ->arrayNode('headers')
                        ->arrayPrototype()
                            ->children()
                                ->scalarNode('name')->isRequired()->cannotBeEmpty()->end()
                                ->scalarNode('value')->isRequired()->cannotBeEmpty()->end()
                            ->end()
                        ->end()
                    ->end()
                    ->enumNode('compression')
                        ->values(array_map(fn (OtlpExporterCompressionEnum $enum) => $enum->value, OtlpExporterCompressionEnum::cases()))
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
