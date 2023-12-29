<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\OtlpExporterCompressionEnum;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\OtlpExporterFormatEnum;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\OtlpExporterOptions;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LoggerProvider\LoggerProviderEnum;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LogProcessor\LogProcessorEnum;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MeterProvider\ExemplarFilterEnum;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MeterProvider\MeterProviderEnum;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MetricExporter\MetricTemporalityEnum;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\SpanProcessor\SpanProcessorEnum;
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
                            ->append($this->getTracingInstrumentationNode())
                            ->append($this->getMeteringInstrumentationNode())
                        ->end()
                    ->end()
                    ->arrayNode('console')
                        ->addDefaultsIfNotSet()
                        ->canBeEnabled()
                        ->children()
                            ->append($this->getTracingInstrumentationNode())
                            ->append($this->getMeteringInstrumentationNode())
                        ->end()
                    ->end()
                    ->arrayNode('messenger')
                        ->addDefaultsIfNotSet()
                        ->canBeEnabled()
                        ->children()
                            ->append($this->getTracingInstrumentationNode())
                            ->append($this->getMeteringInstrumentationNode())
                        ->end()
                    ->end()
                    ->arrayNode('mailer')
                        ->addDefaultsIfNotSet()
                        ->canBeEnabled()
                        ->children()
                            ->append($this->getTracingInstrumentationNode())
                            ->append($this->getMeteringInstrumentationNode())
                        ->end()
                    ->end()
                    ->arrayNode('doctrine')
                        ->addDefaultsIfNotSet()
                        ->canBeEnabled()
                        ->children()
                            ->append($this->getTracingInstrumentationNode())
                            ->append($this->getMeteringInstrumentationNode())
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    private function getTracingInstrumentationNode(): ArrayNodeDefinition
    {
        $treeBuilder = new TreeBuilder('tracing');

        $node = $treeBuilder->getRootNode()
            ->canBeEnabled()
            ->children()
                ->scalarNode('tracer')
                    ->info('The tracer to use, defaults to `traces.default_tracer` or first tracer in `traces.tracers`')
                    ->cannotBeEmpty()
                ->end()
                ->arrayNode('request_headers')
                    ->defaultValue([])
                    ->scalarPrototype()->end()
                ->end()
                ->arrayNode('response_headers')
                    ->defaultValue([])
                    ->scalarPrototype()->end()
                ->end()
            ->end();

        return $node;
    }

    private function getMeteringInstrumentationNode(): ArrayNodeDefinition
    {
        $treeBuilder = new TreeBuilder('metering');

        $node = $treeBuilder->getRootNode()
            ->canBeEnabled()
            ->children()
                ->scalarNode('meter')
                    ->info('The meter to use, defaults to `metrics.default_meter` or first meter in `metrics.meters`')
                    ->cannotBeEmpty()
                ->end()
            ->end();

        return $node;
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
                    ->scalarNode('dsn')
                        ->isRequired()
                        ->cannotBeEmpty()
                    ->end()
                    ->append($this->getOtlpExportersOptionsNode())
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
                    ->scalarNode('dsn')
                        ->isRequired()
                        ->cannotBeEmpty()
                    ->end()
                    ->enumNode('temporality')
                        ->defaultValue(MetricTemporalityEnum::Delta->value)
                        ->values(array_map(fn (MetricTemporalityEnum $temporality) => $temporality->value, MetricTemporalityEnum::cases()))
                    ->end()
                    ->append($this->getOtlpExportersOptionsNode())
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
                    ->scalarNode('dsn')
                        ->isRequired()
                        ->cannotBeEmpty()
                    ->end()
                    ->append($this->getOtlpExportersOptionsNode())
                ->end()
            ->end()
        ;

        return $node;
    }

    private function getOtlpExportersOptionsNode(): ArrayNodeDefinition
    {
        $treeBuilder = new TreeBuilder('options');

        $node = $treeBuilder->getRootNode()
            ->children()
                ->enumNode('format')
                    ->defaultValue(OtlpExporterFormatEnum::Json->value)
                    ->values(array_map(fn (OtlpExporterFormatEnum $format) => $format->value, OtlpExporterFormatEnum::cases()))
                ->end()
                ->enumNode('compression')
                    ->defaultValue(OtlpExporterCompressionEnum::None->value)
                    ->values(array_map(fn (OtlpExporterCompressionEnum $format) => $format->value, OtlpExporterCompressionEnum::cases()))
                ->end()
                ->arrayNode('headers')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('name')->isRequired()->cannotBeEmpty()->end()
                            ->scalarNode('value')->isRequired()->cannotBeEmpty()->end()
                        ->end()
                    ->end()
                ->end()
                ->floatNode('timeout')
                    ->defaultValue(OtlpExporterOptions::DEFAULT_TIMEOUT)
                ->end()
                ->integerNode('retry')
                    ->defaultValue(OtlpExporterOptions::DEFAULT_RETRY_DELAY)
                ->end()
                ->integerNode('max')
                    ->defaultValue(OtlpExporterOptions::DEFAULT_MAX_RETRIES)
                ->end()
                ->scalarNode('ca')
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('cert')
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('key')
                    ->cannotBeEmpty()
                ->end()
            ->end()
        ;

        return $node;
    }
}
