<?php

use FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Doctrine\Middleware\TraceableMiddleware as TraceableDoctrineMiddleware;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Symfony\Cache\TagAwareTraceableCacheAdapter;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Symfony\Cache\TraceableCacheAdapter;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Symfony\Console\ObservableConsoleEventSubscriber;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Symfony\Console\TraceableConsoleEventSubscriber;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Symfony\HttpClient\TraceableHttpClient;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Symfony\HttpKernel\ObservableHttpKernelEventSubscriber;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Symfony\HttpKernel\TraceableHttpKernelEventSubscriber;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Symfony\Mailer\ObservableMailerEventSubscriber;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Symfony\Mailer\TraceableMailer;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Symfony\Mailer\TraceableMailerEventSubscriber;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Symfony\Mailer\TraceableMailerTransport;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Symfony\Messenger\ObservableMessengerEventSubscriber;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Symfony\Messenger\TraceableMessengerEventSubscriber;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Symfony\Messenger\TraceableMessengerMiddleware;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Symfony\Messenger\TraceableMessengerTransport;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Symfony\Messenger\TraceableMessengerTransportFactory;
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
        ->set('monolog.additional_channels', ['open_telemetry'])
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
            ->tag('monolog.logger', ['channel' => 'open_telemetry'])
        ->set('open_telemetry.instrumentation.http_kernel.metric.event_subscriber', ObservableHttpKernelEventSubscriber::class)

        ->set('open_telemetry.instrumentation.console.trace.event_subscriber', TraceableConsoleEventSubscriber::class)
            ->tag('monolog.logger', ['channel' => 'open_telemetry'])

        ->set('open_telemetry.instrumentation.console.metric.event_subscriber', ObservableConsoleEventSubscriber::class)

        ->set('open_telemetry.instrumentation.doctrine.trace.middleware', TraceableDoctrineMiddleware::class)
            ->tag('monolog.logger', ['channel' => 'open_telemetry'])

        ->set('open_telemetry.instrumentation.twig.trace.extension', TraceableTwigExtension::class)

        ->set('open_telemetry.instrumentation.cache.trace.adapter', TraceableCacheAdapter::class)
            ->abstract()
        ->set('open_telemetry.instrumentation.cache.trace.tag_aware_adapter', TagAwareTraceableCacheAdapter::class)
            ->abstract()

        ->set('open_telemetry.instrumentation.http_client.trace.client', TraceableHttpClient::class)

        ->set('open_telemetry.instrumentation.mailer.trace.event_subscriber', TraceableMailerEventSubscriber::class)
        ->tag('monolog.logger', ['channel' => 'open_telemetry'])

        ->set('open_telemetry.instrumentation.mailer.metric.event_subscriber', ObservableMailerEventSubscriber::class)

        ->set('open_telemetry.instrumentation.mailer.trace.transports', TraceableMailerTransport::class)
            ->decorate('mailer.transports')
            ->arg('$transport', service('.inner'))
            ->tag('monolog.logger', ['channel' => 'open_telemetry'])

        ->set('open_telemetry.instrumentation.mailer.trace.default_transport', TraceableMailerTransport::class)
            ->decorate('mailer.default_transport')
            ->arg('$transport', service('.inner'))
            ->tag('monolog.logger', ['channel' => 'open_telemetry'])

        ->set('open_telemetry.instrumentation.mailer.trace.mailer', TraceableMailer::class)
            ->decorate('mailer.mailer')
            ->arg('$mailer', service('.inner'))
            ->tag('monolog.logger', ['channel' => 'open_telemetry'])

        ->set('open_telemetry.instrumentation.messenger.trace.event_subscriber', TraceableMessengerEventSubscriber::class)
        ->set('open_telemetry.instrumentation.messenger.metric.event_subscriber', ObservableMessengerEventSubscriber::class)
        ->set('open_telemetry.instrumentation.messenger.trace.transport', TraceableMessengerTransport::class)
        ->set('open_telemetry.instrumentation.messenger.trace.transport_factory', TraceableMessengerTransportFactory::class)
        ->set('open_telemetry.instrumentation.messenger.trace.middleware', TraceableMessengerMiddleware::class)

        ->alias('messenger.middleware.open_telemetry_tracer', 'open_telemetry.instrumentation.messenger.trace.middleware')

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
