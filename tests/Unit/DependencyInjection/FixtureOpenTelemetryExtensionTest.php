<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Unit\DependencyInjection;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\OpenTelemetryExtension;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\OtlpExporterCompressionEnum;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\OtlpExporterFormatEnum;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\SpanExporter\OtlpSpanExporterFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\SpanProcessor\SimpleSpanProcessorFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;

abstract class FixtureOpenTelemetryExtensionTest extends DependencyInjectionTest
{
    public function testDefaultProviderSimpleProcessorOtlpJsonExporter(): void
    {
        $container = $this->getContainer('default-simple-otlp-json');

        self::assertTrue($container->hasDefinition('open_telemetry.traces.exporters.otlp_json'));
        self::assertTrue($container->hasDefinition('open_telemetry.traces.processors.simple'));
        self::assertTrue($container->hasDefinition('open_telemetry.traces.providers.default'));
        self::assertTrue($container->hasDefinition('open_telemetry.traces.tracers.main'));

        $exporter = $container->getDefinition('open_telemetry.traces.exporters.otlp_json');
        self::assertEquals([OtlpSpanExporterFactory::class, 'create'], $exporter->getFactory());
        self::assertEquals('http://localhost:4318/v1/traces', $exporter->getArgument('$endpoint'));
        self::assertIsArray($exporter->getArgument('$headers'));
        self::assertSame(OtlpExporterFormatEnum::Json, $exporter->getArgument('$format'));
        self::assertSame(OtlpExporterCompressionEnum::None, $exporter->getArgument('$compression'));

        $processor = $container->getDefinition('open_telemetry.traces.processors.simple');
        self::assertEquals([SimpleSpanProcessorFactory::class, 'create'], $processor->getFactory());
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
