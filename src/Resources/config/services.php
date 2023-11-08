<?php

use GaelReyrol\OpenTelemetryBundle\EventSubscriber\ConsoleEventSubscriber;
use GaelReyrol\OpenTelemetryBundle\EventSubscriber\HttpKernelEventSubscriber;
use GaelReyrol\OpenTelemetryBundle\Factory\ConsoleSpanExporterFactory;
use GaelReyrol\OpenTelemetryBundle\Factory\InMemorySpanExporterFactory;
use GaelReyrol\OpenTelemetryBundle\Factory\OtlpSpanExporterFactory;
use GaelReyrol\OpenTelemetryBundle\Factory\ZipkinSpanExporterFactory;
use GaelReyrol\OpenTelemetryBundle\OpenTelemetryBundle;
use OpenTelemetry\SDK\Common\Export\TransportInterface;
use OpenTelemetry\SDK\Trace\NoopTracerProvider;
use OpenTelemetry\SDK\Trace\Sampler\AlwaysOffSampler;
use OpenTelemetry\SDK\Trace\Sampler\AlwaysOnSampler;
use OpenTelemetry\SDK\Trace\Sampler\ParentBased;
use OpenTelemetry\SDK\Trace\Sampler\TraceIdRatioBasedSampler;
use OpenTelemetry\SDK\Trace\SamplerInterface;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;
use OpenTelemetry\SDK\Trace\SpanProcessor\BatchSpanProcessor;
use OpenTelemetry\SDK\Trace\SpanProcessor\MultiSpanProcessor;
use OpenTelemetry\SDK\Trace\SpanProcessor\NoopSpanProcessor;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\SDK\Trace\SpanProcessorInterface;
use OpenTelemetry\SDK\Trace\TracerProvider;
use OpenTelemetry\SDK\Trace\TracerProviderBuilder;
use OpenTelemetry\SDK\Trace\TracerProviderFactory;
use OpenTelemetry\SDK\Trace\TracerProviderInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $container->parameters()
        ->set('open_telemetry.bundle.name', OpenTelemetryBundle::name())
        ->set('open_telemetry.bundle.version', OpenTelemetryBundle::version())

        ->set('open_telemetry.traces.providers.default.class', TracerProvider::class)
        ->set('open_telemetry.traces.providers.default.builder.class', TracerProviderBuilder::class)
        ->set('open_telemetry.traces.providers.default.factory.class', TracerProviderFactory::class)
        ->set('open_telemetry.traces.providers.noop.class', NoopTracerProvider::class)

        ->set('open_telemetry.traces.samplers.always_on.class', AlwaysOnSampler::class)
        ->set('open_telemetry.traces.samplers.always_off.class', AlwaysOffSampler::class)
        ->set('open_telemetry.traces.samplers.parent_based.class', ParentBased::class)
        ->set('open_telemetry.traces.samplers.trace_id_ratio_based.class', TraceIdRatioBasedSampler::class)

        ->set('open_telemetry.traces.processors.batch.class', BatchSpanProcessor::class)
        ->set('open_telemetry.traces.processors.multi.class', MultiSpanProcessor::class)
        ->set('open_telemetry.traces.processors.simple.class', SimpleSpanProcessor::class)
        ->set('open_telemetry.traces.processors.noop.class', NoopSpanProcessor::class)

        ->set('open_telemetry.traces.exporters.in_memory.factory.class', InMemorySpanExporterFactory::class)

        ->set('open_telemetry.traces.exporters.console.factory.class', ConsoleSpanExporterFactory::class)
        ->set('open_telemetry.traces.exporters.otlp.factory.class', OtlpSpanExporterFactory::class)
        ->set('open_telemetry.traces.exporters.zipkin.factory.class', ZipkinSpanExporterFactory::class)
    ;

    $container->services()
        ->defaults()
        ->private()

        ->set('open_telemetry.instrumentation.http_kernel.event_subscriber', HttpKernelEventSubscriber::class)
        ->set('open_telemetry.instrumentation.console.event_subscriber', ConsoleEventSubscriber::class)

        ->set('open_telemetry.traces.provider', TracerProviderInterface::class)
        ->set('open_telemetry.traces.sampler', SamplerInterface::class)
        ->set('open_telemetry.traces.processor', SpanProcessorInterface::class)
        ->set('open_telemetry.traces.exporter', SpanExporterInterface::class)
        ->set('open_telemetry.traces.transport', TransportInterface::class)
    ;
};
