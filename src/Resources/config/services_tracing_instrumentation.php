<?php

use FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Doctrine\EventSubscriber\TraceableEntityEventSubscriber as TraceableDoctrineEntityEventSubscriber;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Doctrine\Middleware\TraceableMiddleware as TraceableDoctrineMiddleware;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Symfony\Cache\TagAwareTraceableCacheAdapter;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Symfony\Cache\TraceableCacheAdapter;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Symfony\Console\TraceableConsoleEventSubscriber;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Symfony\HttpClient\TraceableHttpClient;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Symfony\HttpKernel\TraceableHttpKernelEventSubscriber;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Symfony\Mailer\TraceableMailer;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Symfony\Mailer\TraceableMailerEventSubscriber;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Symfony\Mailer\TraceableMailerTransport;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Symfony\Messenger\TraceableMessengerEventSubscriber;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Symfony\Messenger\TraceableMessengerMiddleware;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Symfony\Messenger\TraceableMessengerTransport;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Symfony\Messenger\TraceableMessengerTransportFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Twig\TraceableTwigExtension;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $container->services()
        ->defaults()
        ->private()

        ->set('open_telemetry.instrumentation.http_kernel.trace.event_subscriber', TraceableHttpKernelEventSubscriber::class)
            ->arg('$tracer', service('open_telemetry.traces.default_tracer'))
            ->arg('$propagator', service('open_telemetry.text_map_propagators.noop'))
            ->arg('$propagationGetter', service('open_telemetry.propagation_getters.headers'))
            ->arg('$requestHeaders', param('open_telemetry.instrumentation.http_kernel.tracing.request_headers'))
            ->arg('$responseHeaders', param('open_telemetry.instrumentation.http_kernel.tracing.response_headers'))
            ->tag('kernel.event_subscriber')
            ->tag('monolog.logger', ['channel' => 'open_telemetry'])

        ->set('open_telemetry.instrumentation.console.trace.event_subscriber', TraceableConsoleEventSubscriber::class)
            ->arg('$tracer', service('open_telemetry.traces.default_tracer'))
            ->tag('kernel.event_subscriber')

        ->set('open_telemetry.instrumentation.doctrine.trace.event_subscriber', TraceableDoctrineEntityEventSubscriber::class)
            ->arg('$tracer', service('open_telemetry.traces.default_tracer'))
            ->tag('doctrine.event_subscriber')
            ->tag('monolog.logger', ['channel' => 'open_telemetry'])

        ->set('open_telemetry.instrumentation.doctrine.trace.middleware', TraceableDoctrineMiddleware::class)
            ->arg('$tracer', service('open_telemetry.traces.default_tracer'))
            ->tag('doctrine.middleware')
            ->tag('monolog.logger', ['channel' => 'open_telemetry'])

        ->set('open_telemetry.instrumentation.twig.trace.extension', TraceableTwigExtension::class)
            ->arg('$tracer', service('open_telemetry.traces.default_tracer'))
            ->tag('twig.extension')
            ->tag('monolog.logger', ['channel' => 'open_telemetry'])

        ->set('open_telemetry.instrumentation.cache.trace.adapter', TraceableCacheAdapter::class)
            ->arg('$tracer', service('open_telemetry.traces.default_tracer'))
            ->abstract()
            ->tag('monolog.logger', ['channel' => 'open_telemetry'])

        ->set('open_telemetry.instrumentation.cache.trace.tag_aware_adapter', TagAwareTraceableCacheAdapter::class)
            ->arg('$tracer', service('open_telemetry.traces.default_tracer'))
            ->abstract()
            ->tag('monolog.logger', ['channel' => 'open_telemetry'])

        ->set('open_telemetry.instrumentation.http_client.trace.client', TraceableHttpClient::class)
            ->arg('$tracer', service('open_telemetry.traces.default_tracer'))
            ->tag('monolog.logger', ['channel' => 'open_telemetry'])

        ->set('open_telemetry.instrumentation.mailer.trace.event_subscriber', TraceableMailerEventSubscriber::class)
            ->arg('$tracer', service('open_telemetry.traces.default_tracer'))
            ->tag('kernel.event_subscriber')
            ->tag('monolog.logger', ['channel' => 'open_telemetry'])

        ->set('open_telemetry.instrumentation.mailer.trace.default_transport', TraceableMailerTransport::class)
            ->decorate('mailer.default_transport')
            ->arg('$tracer', service('open_telemetry.traces.default_tracer'))
            ->arg('$transport', service('.inner'))
            ->tag('monolog.logger', ['channel' => 'open_telemetry'])

        ->set('open_telemetry.instrumentation.mailer.trace.mailer', TraceableMailer::class)
            ->decorate('mailer.mailer')
            ->arg('$tracer', service('open_telemetry.traces.default_tracer'))
            ->arg('$mailer', service('.inner'))
            ->tag('monolog.logger', ['channel' => 'open_telemetry'])

        ->set('open_telemetry.instrumentation.messenger.trace.event_subscriber', TraceableMessengerEventSubscriber::class)
            ->arg('$tracer', service('open_telemetry.traces.default_tracer'))
            ->tag('kernel.event_subscriber')
            ->tag('monolog.logger', ['channel' => 'open_telemetry'])

        ->set('open_telemetry.instrumentation.messenger.trace.transport', TraceableMessengerTransport::class)
            ->arg('$tracer', service('open_telemetry.traces.default_tracer'))
            ->tag('monolog.logger', ['channel' => 'open_telemetry'])

        ->set('open_telemetry.instrumentation.messenger.trace.transport_factory', TraceableMessengerTransportFactory::class)
            ->arg('$tracer', service('open_telemetry.traces.default_tracer'))
            ->arg('$transportFactory', service('messenger.transport_factory'))
            ->tag('messenger.transport_factory')
            ->tag('kernel.reset', ['method' => 'reset'])
            ->tag('monolog.logger', ['channel' => 'open_telemetry'])

        ->set('open_telemetry.instrumentation.messenger.trace.middleware', TraceableMessengerMiddleware::class)
            ->arg('$tracer', service('open_telemetry.traces.default_tracer'))
            ->tag('monolog.logger', ['channel' => 'open_telemetry'])

        ->alias('messenger.transport.open_telemetry_tracer.factory', 'open_telemetry.instrumentation.messenger.trace.transport_factory')
        ->alias('messenger.middleware.open_telemetry_tracer', 'open_telemetry.instrumentation.messenger.trace.middleware')
    ;
};
