<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Unit\DependencyInjection;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\OpenTelemetryExtension;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LogExporter\OtlpLogExporterFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LoggerProvider\LoggerProviderFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LoggerProvider\NoopLoggerProviderFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LogProcessor\SimpleLogProcessorFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MeterProvider\MeterProviderFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MeterProvider\NoopMeterProviderFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MetricExporter\OtlpMetricExporterFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\SpanExporter\OtlpSpanExporterFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\SpanProcessor\SimpleSpanProcessorFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\TracerProvider\NoopTracerProviderFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\TracerProvider\TracerProviderFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

abstract class FixtureOpenTelemetryExtensionTest extends DependencyInjectionTest
{
    public function testNoop(): void
    {
        $container = $this->getContainer('noop');

        self::assertTrue($container->hasDefinition('open_telemetry.traces.tracers.main'));
        self::assertTrue($container->hasDefinition('open_telemetry.traces.providers.noop'));

        $provider = $container->getDefinition('open_telemetry.traces.providers.noop');
        self::assertEquals([NoopTracerProviderFactory::class, 'createProvider'], $provider->getFactory());

        $tracer = $container->getDefinition('open_telemetry.traces.tracers.main');
        self::assertEquals([
            new Reference('open_telemetry.traces.providers.noop'),
            'getTracer',
        ], $tracer->getFactory());

        self::assertTrue($container->hasDefinition('open_telemetry.metrics.meters.main'));
        self::assertTrue($container->hasDefinition('open_telemetry.metrics.providers.noop'));

        $provider = $container->getDefinition('open_telemetry.metrics.providers.noop');
        self::assertEquals([NoopMeterProviderFactory::class, 'createProvider'], $provider->getFactory());

        $tracer = $container->getDefinition('open_telemetry.metrics.meters.main');
        self::assertEquals([
            new Reference('open_telemetry.metrics.providers.noop'),
            'getMeter',
        ], $tracer->getFactory());

        self::assertTrue($container->hasDefinition('open_telemetry.logs.loggers.main'));
        self::assertTrue($container->hasDefinition('open_telemetry.logs.providers.noop'));

        $provider = $container->getDefinition('open_telemetry.logs.providers.noop');
        self::assertEquals([NoopLoggerProviderFactory::class, 'createProvider'], $provider->getFactory());

        $tracer = $container->getDefinition('open_telemetry.logs.loggers.main');
        self::assertEquals([
            new Reference('open_telemetry.logs.providers.noop'),
            'getLogger',
        ], $tracer->getFactory());
    }

    public function testTracesDefaultProviderSimpleProcessorOtlpExporter(): void
    {
        $container = $this->getContainer('traces-default-simple-otlp');

        self::assertTrue($container->hasDefinition('open_telemetry.traces.exporters.otlp'));
        self::assertTrue($container->hasDefinition('open_telemetry.traces.processors.simple'));
        self::assertTrue($container->hasDefinition('open_telemetry.traces.providers.default'));
        self::assertTrue($container->hasDefinition('open_telemetry.traces.tracers.main'));

        $exporter = $container->getDefinition('open_telemetry.traces.exporters.otlp');
        self::assertEquals([OtlpSpanExporterFactory::class, 'createExporter'], $exporter->getFactory());
        self::assertEquals(['http+otlp://localhost'], $exporter->getArgument('$dsn')->getArguments());
        self::assertEquals([[]], $exporter->getArgument('$options')->getArguments());

        $processor = $container->getDefinition('open_telemetry.traces.processors.simple');
        self::assertEquals([SimpleSpanProcessorFactory::class, 'createProcessor'], $processor->getFactory());

        $provider = $container->getDefinition('open_telemetry.traces.providers.default');
        self::assertEquals([TracerProviderFactory::class, 'createProvider'], $provider->getFactory());

        $tracer = $container->getDefinition('open_telemetry.traces.tracers.main');
        self::assertEquals([
            new Reference('open_telemetry.traces.providers.default'),
            'getTracer',
        ], $tracer->getFactory());
    }

    public function testMetricsDefaultProviderOtlpExporter(): void
    {
        $container = $this->getContainer('metrics-default-otlp');

        self::assertTrue($container->hasDefinition('open_telemetry.metrics.exporters.otlp'));
        self::assertTrue($container->hasDefinition('open_telemetry.metrics.providers.default'));
        self::assertTrue($container->hasDefinition('open_telemetry.metrics.meters.main'));

        $exporter = $container->getDefinition('open_telemetry.metrics.exporters.otlp');
        self::assertEquals([OtlpMetricExporterFactory::class, 'createExporter'], $exporter->getFactory());
        self::assertEquals(['http+otlp://localhost'], $exporter->getArgument('$dsn')->getArguments());
        self::assertEquals([[]], $exporter->getArgument('$options')->getArguments());

        $provider = $container->getDefinition('open_telemetry.metrics.providers.default');
        self::assertEquals([MeterProviderFactory::class, 'createProvider'], $provider->getFactory());

        $tracer = $container->getDefinition('open_telemetry.metrics.meters.main');
        self::assertEquals([
            new Reference('open_telemetry.metrics.providers.default'),
            'getMeter',
        ], $tracer->getFactory());
    }

    public function testLogsDefaultProviderSimpleProcessorOtlpExporter(): void
    {
        $container = $this->getContainer('logs-default-simple-otlp');

        self::assertTrue($container->hasDefinition('open_telemetry.logs.exporters.otlp'));
        self::assertTrue($container->hasDefinition('open_telemetry.logs.processors.simple'));
        self::assertTrue($container->hasDefinition('open_telemetry.logs.providers.default'));
        self::assertTrue($container->hasDefinition('open_telemetry.logs.loggers.main'));

        $exporter = $container->getDefinition('open_telemetry.logs.exporters.otlp');
        self::assertEquals([OtlpLogExporterFactory::class, 'createExporter'], $exporter->getFactory());
        self::assertEquals(['http+otlp://localhost'], $exporter->getArgument('$dsn')->getArguments());
        self::assertEquals([[]], $exporter->getArgument('$options')->getArguments());

        $processor = $container->getDefinition('open_telemetry.logs.processors.simple');
        self::assertEquals([SimpleLogProcessorFactory::class, 'createProcessor'], $processor->getFactory());

        $provider = $container->getDefinition('open_telemetry.logs.providers.default');
        self::assertEquals([LoggerProviderFactory::class, 'createProvider'], $provider->getFactory());

        $tracer = $container->getDefinition('open_telemetry.logs.loggers.main');
        self::assertEquals([
            new Reference('open_telemetry.logs.providers.default'),
            'getLogger',
        ], $tracer->getFactory());
    }

    protected function getContainer(string $fixture): ContainerBuilder
    {
        $container = new ContainerBuilder();
        $container->registerExtension(new OpenTelemetryExtension());

        $this->loadFixture($container, $fixture);

        $container->getCompilerPassConfig()->setOptimizationPasses([]);
        $container->getCompilerPassConfig()->setRemovingPasses([]);
        $container->compile();

        return $container;
    }

    abstract protected function loadFixture(ContainerBuilder $container, string $fixture): void;
}
