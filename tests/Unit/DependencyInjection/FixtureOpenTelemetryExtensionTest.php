<?php

namespace GaelReyrol\OpenTelemetryBundle\Tests\Unit\DependencyInjection;

use GaelReyrol\OpenTelemetryBundle\DependencyInjection\OpenTelemetryExtension;
use GaelReyrol\OpenTelemetryBundle\DependencyInjection\OtlpExporterCompressionEnum;
use GaelReyrol\OpenTelemetryBundle\DependencyInjection\OtlpExporterFormatEnum;
use GaelReyrol\OpenTelemetryBundle\DependencyInjection\SpanProcessorEnum;
use GaelReyrol\OpenTelemetryBundle\DependencyInjection\TraceExporterEnum;
use GaelReyrol\OpenTelemetryBundle\Factory\SpanExporter\OtlpSpanExporterFactory;
use GaelReyrol\OpenTelemetryBundle\Factory\SpanProcessor\SimpleSpanProcessorFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

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
        self::assertSame(TraceExporterEnum::Otlp, $exporter->getArgument('type'));
        self::assertEquals('http://localhost:4318/v1/traces', $exporter->getArgument('endpoint'));
        self::assertIsArray($exporter->getArgument('headers'));
        self::assertSame(OtlpExporterFormatEnum::Json, $exporter->getArgument('format'));
        self::assertSame(OtlpExporterCompressionEnum::None, $exporter->getArgument('compression'));

        $processor = $container->getDefinition('open_telemetry.traces.processors.simple');
        self::assertEquals([SimpleSpanProcessorFactory::class, 'create'], $processor->getFactory());
        self::assertSame(SpanProcessorEnum::Simple, $processor->getArgument('type'));
        $exporterReference = $processor->getArgument('exporter');
        self::assertInstanceOf(Reference::class, $exporterReference);
        self::assertEquals(0, (new Reference('open_telemetry.traces.processors.simple'))->getInvalidBehavior());
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
