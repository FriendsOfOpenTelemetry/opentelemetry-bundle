<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LogExporter\LogExporterEnum;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LoggerProvider\LoggerProviderEnum;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LogProcessor\LogProcessorEnum;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MeterProvider\ExemplarFilterEnum;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MeterProvider\MeterProviderEnum;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MetricExporter\MetricExporterEnum;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MetricExporter\MetricTemporalityEnum;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\OtlpExporterCompressionEnum;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\OtlpExporterFormatEnum;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\SpanProcessorEnum;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\SpanExporter\TraceExporterEnum;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\TracerProvider\TraceProviderEnum;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\TracerProvider\TraceSamplerEnum;
use Monolog\Level;
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
        $this->addMetricsSection($rootNode);
        $this->addLogsSection($rootNode);

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
                        ->cannotBeEmpty()
                        ->isRequired()
                        ->example('%kernel.environment%')
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
                        ->addDefaultsIfNotSet()
                        ->canBeEnabled()
                        ->children()
                            ->arrayNode('tracing')
                                ->canBeEnabled()
                                ->children()
                                    ->scalarNode('tracer')
                                        ->info('The tracer to use, defaults to `traces.default_tracer` or first tracer in `traces.tracers`')
                                        ->cannotBeEmpty()
                                        ->isRequired()
                                    ->end()
                                    ->arrayNode('request_headers')
                                        ->defaultValue([])
                                        ->scalarPrototype()->end()
                                    ->end()
                                    ->arrayNode('response_headers')
                                        ->defaultValue([])
                                        ->scalarPrototype()->end()
                                    ->end()
                                ->end()
                            ->end()
                            ->arrayNode('metering')
                                ->canBeEnabled()
                                ->children()
                                    ->scalarNode('meter')
                                        ->info('The meter to use, defaults to `metrics.default_meter` or first meter in `metrics.meters`')
                                        ->cannotBeEmpty()
                                        ->isRequired()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('console')
                        ->addDefaultsIfNotSet()
                        ->canBeEnabled()
                        ->children()
                            ->arrayNode('tracing')
                                ->canBeEnabled()
                                ->children()
                                    ->scalarNode('tracer')
                                        ->info('The tracer to use, defaults to `traces.default_tracer` or first tracer in `traces.tracers`')
                                        ->cannotBeEmpty()
                                        ->isRequired()
                                    ->end()
                                ->end()
                            ->end()
                            ->arrayNode('metering')
                                ->canBeEnabled()
                                ->children()
                                    ->scalarNode('meter')
                                        ->info('The meter to use, defaults to `metrics.default_meter` or first meter in `metrics.meters`')
                                        ->cannotBeEmpty()
                                        ->isRequired()
                                    ->end()
                                ->end()
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
                ->children()
                    ->scalarNode('default_tracer')
                        ->info('The default tracer to use among the `tracers`')
                        ->cannotBeEmpty()
                    ->end()
                    ->append($this->getTracingTracersNode())
                    ->append($this->getTracingProvidersNode())
                    ->append($this->getTracingProcessorsNode())
                    ->append($this->getTracingExportersNode())
                ->end()
            ->end()
        ;
    }

    private function getTracingTracersNode(): ArrayNodeDefinition
    {
        $treeBuilder = new TreeBuilder('tracers');

        $node = $treeBuilder->getRootNode()
            ->requiresAtLeastOneElement()
            ->useAttributeAsKey('tracer')
            ->arrayPrototype()
                ->children()
                    ->scalarNode('name')->cannotBeEmpty()->end()
                    ->scalarNode('version')->cannotBeEmpty()->end()
                    ->scalarNode('provider')
                        ->cannotBeEmpty()
                        ->isRequired()
                    ->end()
                ->end()
            ->end();

        return $node;
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
                            ->floatNode('ratio')->end()
                            ->scalarNode('parent')->cannotBeEmpty()->end()
                        ->end()
                    ->end()
                    ->arrayNode('processors')
                        ->requiresAtLeastOneElement()
                        ->scalarPrototype()->cannotBeEmpty()->isRequired()->end()
                    ->end()
                ->end()
            ->end();

        return $node;
    }

    private function getTracingProcessorsNode(): ArrayNodeDefinition
    {
        $treeBuilder = new TreeBuilder('processors');

        $node = $treeBuilder->getRootNode()
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
                        ->cannotBeEmpty()
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
                    ->end()
                    ->enumNode('format')
                        ->info(sprintf('Required if exporter type is %s', OtlpExporterFormatEnum::Json->value))
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

    private function addMetricsSection(ArrayNodeDefinition $node): void
    {
        $node
            ->children()
                ->arrayNode('metrics')
                ->addDefaultsIfNotSet()
                ->children()
                    ->scalarNode('default_meter')
                        ->info('The default meter to use among the `meters`')
                        ->cannotBeEmpty()
                    ->end()
                    ->append($this->getMetricMetersNode())
                    ->append($this->getMetricProvidersNode())
                    ->append($this->getMetricExportersNode())
                ->end()
            ->end()
        ;
    }

    private function getMetricMetersNode(): ArrayNodeDefinition
    {
        $treeBuilder = new TreeBuilder('meters');

        $node = $treeBuilder->getRootNode()
            ->requiresAtLeastOneElement()
            ->useAttributeAsKey('meter')
            ->arrayPrototype()
                ->children()
                    ->scalarNode('name')
                        ->cannotBeEmpty()
                    ->end()
                    ->scalarNode('provider')
                        ->cannotBeEmpty()
                        ->isRequired()
                    ->end()
                ->end()
            ->end();

        return $node;
    }

    private function getMetricProvidersNode(): ArrayNodeDefinition
    {
        $treeBuilder = new TreeBuilder('providers');

        $node = $treeBuilder->getRootNode()
            ->requiresAtLeastOneElement()
            ->useAttributeAsKey('provider')
            ->arrayPrototype()
                ->children()
                    ->enumNode('type')
                        ->defaultValue(MeterProviderEnum::Default->value)
                        ->values(array_map(fn (MeterProviderEnum $enum) => $enum->value, MeterProviderEnum::cases()))
                        ->isRequired()
                    ->end()
                    ->scalarNode('exporter')
                        ->cannotBeEmpty()
                    ->end()
                    ->enumNode('filter')
                        ->defaultValue(ExemplarFilterEnum::None->value)
                        ->values(array_map(fn (ExemplarFilterEnum $enum) => $enum->value, ExemplarFilterEnum::cases()))
                    ->end()
                ->end()
            ->end();

        return $node;
    }

    private function getMetricExportersNode(): ArrayNodeDefinition
    {
        $treeBuilder = new TreeBuilder('exporters');

        $node = $treeBuilder->getRootNode()
            ->useAttributeAsKey('exporter')
            ->arrayPrototype()
                ->children()
                    ->enumNode('type')
                        ->defaultValue(MetricExporterEnum::Otlp->value)
                        ->values(array_map(fn (MetricExporterEnum $enum) => $enum->value, MetricExporterEnum::cases()))
                        ->isRequired()
                    ->end()
                    ->scalarNode('endpoint')
                        ->cannotBeEmpty()
                    ->end()
                    ->enumNode('format')
                        ->info(sprintf('Required if exporter type is %s', OtlpExporterFormatEnum::Json->value))
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
                    ->enumNode('temporality')
                        ->values(array_map(fn (MetricTemporalityEnum $enum) => $enum->value, MetricTemporalityEnum::cases()))
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
                ->children()
                    ->scalarNode('default_logger')
                        ->info('The default logger to use among the `loggers`')
                        ->cannotBeEmpty()
                    ->end()
                    ->append($this->getLogsMonologNode())
                    ->append($this->getLogsLoggersNode())
                    ->append($this->getLogsProvidersNode())
                    ->append($this->getLogsProcessorsNode())
                    ->append($this->getLogsExportersNode())
                ->end()
            ->end()
        ;
    }

    private function getLogsMonologNode(): ArrayNodeDefinition
    {
        $treeBuilder = new TreeBuilder('monolog');

        $node = $treeBuilder->getRootNode()
            ->canBeEnabled()
            ->children()
                ->arrayNode('handlers')
                    ->requiresAtLeastOneElement()
                    ->useAttributeAsKey('handler')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('provider')->cannotBeEmpty()->isRequired()->end()
                            ->enumNode('level')
                                ->defaultValue(strtolower(Level::Debug->name))
                                ->values(array_map(fn (Level $level) => strtolower($level->name), Level::cases()))
                            ->end()
                            ->booleanNode('bubble')->defaultValue(true)->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $node;
    }

    private function getLogsLoggersNode(): ArrayNodeDefinition
    {
        $treeBuilder = new TreeBuilder('loggers');

        $node = $treeBuilder->getRootNode()
            ->requiresAtLeastOneElement()
            ->useAttributeAsKey('logger')
            ->arrayPrototype()
                ->children()
                    ->scalarNode('name')->cannotBeEmpty()->end()
                    ->scalarNode('version')->cannotBeEmpty()->end()
                    ->scalarNode('provider')
                        ->cannotBeEmpty()
                        ->isRequired()
                    ->end()
                ->end()
            ->end();

        return $node;
    }

    private function getLogsProvidersNode(): ArrayNodeDefinition
    {
        $treeBuilder = new TreeBuilder('providers');

        $node = $treeBuilder->getRootNode()
            ->requiresAtLeastOneElement()
            ->useAttributeAsKey('provider')
            ->arrayPrototype()
                ->children()
                    ->enumNode('type')
                        ->defaultValue(LoggerProviderEnum::Default->value)
                        ->values(array_map(fn (LoggerProviderEnum $enum) => $enum->value, LoggerProviderEnum::cases()))
                        ->isRequired()
                    ->end()
                    ->scalarNode('processor')->cannotBeEmpty()->end()
                ->end()
            ->end();

        return $node;
    }

    private function getLogsProcessorsNode(): ArrayNodeDefinition
    {
        $treeBuilder = new TreeBuilder('processors');

        $node = $treeBuilder->getRootNode()
            ->useAttributeAsKey('processor')
            ->arrayPrototype()
                ->children()
                    ->enumNode('type')
                        ->defaultValue(LogProcessorEnum::Simple->value)
                        ->values(array_map(fn (LogProcessorEnum $enum) => $enum->value, LogProcessorEnum::cases()))
                        ->isRequired()
                    ->end()
                    ->arrayNode('processors')
                        ->info('Required if processor type is multi')
                    ->end()
                    ->scalarNode('exporter')
                        ->info('Required if processor type is simple or batch')
                        ->cannotBeEmpty()
                    ->end()
                ->end()
            ->end()
        ;

        return $node;
    }

    private function getLogsExportersNode(): ArrayNodeDefinition
    {
        $treeBuilder = new TreeBuilder('exporters');

        $node = $treeBuilder->getRootNode()
            ->useAttributeAsKey('exporter')
            ->arrayPrototype()
                ->children()
                    ->enumNode('type')
                        ->defaultValue(LogExporterEnum::Otlp->value)
                        ->values(array_map(fn (LogExporterEnum $enum) => $enum->value, LogExporterEnum::cases()))
                        ->isRequired()
                    ->end()
                    ->scalarNode('endpoint')
                        ->cannotBeEmpty()
                    ->end()
                    ->enumNode('format')
                        ->info(sprintf('Required if exporter type is %s', OtlpExporterFormatEnum::Json->value))
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
}
