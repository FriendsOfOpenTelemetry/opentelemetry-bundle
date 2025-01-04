<?php

use FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Symfony\Framework\Routing\TraceableRouteLoader;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Symfony\HttpKernel\TraceableHttpKernelEventSubscriber;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $container->services()
        ->defaults()
        ->private()
        ->set('open_telemetry.instrumentation.http_kernel.trace.event_subscriber', TraceableHttpKernelEventSubscriber::class)
            ->arg('$tracer', service('open_telemetry.traces.default_tracer'))
            ->arg('$propagator', service('open_telemetry.propagator_text_map.noop'))
            ->arg('$propagationGetter', service('open_telemetry.propagation_getter.headers'))
            ->tag('kernel.event_subscriber')
            ->tag('monolog.logger', ['channel' => 'open_telemetry'])

        ->set('open_telemetry.instrumentation.http_kernel.trace.route_loader', TraceableRouteLoader::class)
            ->decorate('routing.loader')
            ->arg('$loader', service('.inner'))
            ->tag('monolog.logger', ['channel' => 'open_telemetry'])
    ;
};
