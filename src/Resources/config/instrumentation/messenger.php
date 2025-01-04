<?php

use FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Symfony\Messenger\TraceableMessengerMiddleware;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Symfony\Messenger\TraceableMessengerTransport;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Symfony\Messenger\TraceableMessengerTransportFactory;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $container->services()
        ->defaults()
        ->private()
        ->set('open_telemetry.instrumentation.messenger.trace.transport', TraceableMessengerTransport::class)
            ->arg('$tracer', service('open_telemetry.traces.default_tracer'))
            ->tag('monolog.logger', ['channel' => 'open_telemetry'])

        ->set('open_telemetry.instrumentation.messenger.trace.transport_factory', TraceableMessengerTransportFactory::class)
            ->arg('$tracer', service('open_telemetry.traces.default_tracer'))
            ->arg('$transportFactory', service('messenger.transport_factory'))
            ->tag('messenger.transport_factory')
            ->tag('kernel.reset', ['method' => 'reset'])
            ->tag('monolog.logger', ['channel' => 'open_telemetry'])
        ->alias('messenger.transport.open_telemetry_tracer.factory', 'open_telemetry.instrumentation.messenger.trace.transport_factory')

        ->set('open_telemetry.instrumentation.messenger.trace.middleware', TraceableMessengerMiddleware::class)
            ->arg('$tracer', service('open_telemetry.traces.default_tracer'))
            ->tag('monolog.logger', ['channel' => 'open_telemetry'])
        ->alias('messenger.middleware.open_telemetry_tracer', 'open_telemetry.instrumentation.messenger.trace.middleware')
    ;
};
