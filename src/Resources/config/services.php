<?php

use FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Doctrine\Middleware\TraceableMiddleware as TraceableDoctrineMiddleware;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Symfony\Adapter\Cache\TagAwareTraceableCacheAdapter;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Symfony\Adapter\Cache\TraceableCacheAdapter;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Symfony\EventSubscriber\Console\ConsoleMetricEventSubscriber;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Symfony\EventSubscriber\Console\TraceableConsoleEventSubscriber;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Symfony\EventSubscriber\HttpKernel\HttpKernelMetricEventSubscriber;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Symfony\EventSubscriber\HttpKernel\TraceableHttpKernelEventSubscriber;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Twig\TraceableTwigExtension;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Context\Propagator\HeadersPropagator;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterDsn;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterOptionsInterface;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\OtlpExporterOptions;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MetricExporterOptions;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetryBundle;
use OpenTelemetry\Context\Propagation\NoopTextMapPropagator;
use OpenTelemetry\Contrib\Logs\Monolog\Handler as MonologHandler;
use OpenTelemetry\SDK\Logs\Logger;
use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilter\AllExemplarFilter;
use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilter\NoneExemplarFilter;
use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilter\WithSampledTraceExemplarFilter;
use OpenTelemetry\SDK\Metrics\Meter;
use OpenTelemetry\SDK\Trace\Sampler\AlwaysOffSampler;
use OpenTelemetry\SDK\Trace\Sampler\AlwaysOnSampler;
use OpenTelemetry\SDK\Trace\Sampler\ParentBased;
use OpenTelemetry\SDK\Trace\Sampler\TraceIdRatioBasedSampler;
use OpenTelemetry\SDK\Trace\Tracer;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $container->parameters()
        ->set('open_telemetry.bundle.name', OpenTelemetryBundle::name())
        ->set('open_telemetry.bundle.version', OpenTelemetryBundle::version())
    ;

    $container->services()
        ->defaults()
        ->private()

        ->set('open_telemetry.exporter_dsn', ExporterDsn::class)
            ->abstract()
            ->factory([ExporterDsn::class, 'fromString'])

        ->set('open_telemetry.exporter_options', ExporterOptionsInterface::class)
            ->factory([OtlpExporterOptions::class, 'fromConfiguration'])
        ->set('open_telemetry.metric_exporter_options', ExporterOptionsInterface::class)
            ->factory([MetricExporterOptions::class, 'fromConfiguration'])

        ->set('open_telemetry.text_map_propagators.noop', NoopTextMapPropagator::class)
        ->set('open_telemetry.propagation_getters.headers', HeadersPropagator::class)

        ->set('open_telemetry.instrumentation.http_kernel.trace.event_subscriber', TraceableHttpKernelEventSubscriber::class)
            ->arg('$propagator', service('open_telemetry.text_map_propagators.noop'))
            ->arg('$propagationGetter', service('open_telemetry.propagation_getters.headers'))
            ->arg('$requestHeaders', param('open_telemetry.instrumentation.http_kernel.request_headers'))
            ->arg('$responseHeaders', param('open_telemetry.instrumentation.http_kernel.response_headers'))
        ->set('open_telemetry.instrumentation.http_kernel.metric.event_subscriber', HttpKernelMetricEventSubscriber::class)

        ->set('open_telemetry.instrumentation.console.trace.event_subscriber', TraceableConsoleEventSubscriber::class)
        ->set('open_telemetry.instrumentation.console.metric.event_subscriber', ConsoleMetricEventSubscriber::class)

        ->set('open_telemetry.instrumentation.doctrine.trace.middleware', TraceableDoctrineMiddleware::class)

        ->set('open_telemetry.instrumentation.twig.trace.extension', TraceableTwigExtension::class)

        ->set('open_telemetry.instrumentation.cache.trace.adapter', TraceableCacheAdapter::class)
            ->abstract()
        ->set('open_telemetry.instrumentation.cache.trace.tag_aware_adapter', TagAwareTraceableCacheAdapter::class)
            ->abstract()

        ->set('open_telemetry.traces.samplers.always_on', AlwaysOnSampler::class)->public()
        ->set('open_telemetry.traces.samplers.always_off', AlwaysOffSampler::class)->public()
        ->set('open_telemetry.traces.samplers.trace_id_ratio_based', TraceIdRatioBasedSampler::class)->public()
        ->set('open_telemetry.traces.samplers.parent_based', ParentBased::class)->public()

        ->set('open_telemetry.traces.exporter' /* , SpanExporterInterface::class */)
            ->synthetic()
        ->set('open_telemetry.traces.processor' /* , SpanProcessorInterface::class */)
            ->synthetic()
        ->set('open_telemetry.traces.provider' /* , TracerProviderInterface::class */)
            ->synthetic()
        ->set('open_telemetry.traces.tracer', Tracer::class)
            ->synthetic()

        ->set('open_telemetry.metrics.exemplar_filters.with_sampled_trace', WithSampledTraceExemplarFilter::class)->public()
        ->set('open_telemetry.metrics.exemplar_filters.all', AllExemplarFilter::class)->public()
        ->set('open_telemetry.metrics.exemplar_filters.none', NoneExemplarFilter::class)->public()

        ->set('open_telemetry.metrics.exporter' /* , MetricExporterInterface::class */)
            ->synthetic()
        ->set('open_telemetry.metrics.provider' /* , MeterProviderInterface::class */)
            ->synthetic()
        ->set('open_telemetry.metrics.meter', Meter::class)
            ->synthetic()

        ->set('open_telemetry.logs.exporter' /* , LogRecordExporterInterface::class */)
            ->synthetic()
        ->set('open_telemetry.logs.processor' /* , LogRecordProcessorInterface::class */)
            ->synthetic()
        ->set('open_telemetry.logs.provider' /* , LoggerProviderInterface::class */)
            ->synthetic()
        ->set('open_telemetry.logs.logger', Logger::class)
            ->synthetic()

        ->set('open_telemetry.logs.monolog.handler', MonologHandler::class)
    ;
};
