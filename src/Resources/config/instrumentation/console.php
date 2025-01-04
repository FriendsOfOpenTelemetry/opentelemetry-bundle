<?php

use FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Symfony\Console\TraceableConsoleEventSubscriber;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $container->services()
        ->defaults()
        ->private()
        ->set('open_telemetry.instrumentation.console.trace.event_subscriber', TraceableConsoleEventSubscriber::class)
            ->arg('$tracer', service('open_telemetry.traces.default_tracer'))
            ->tag('kernel.event_subscriber')
    ;
};
