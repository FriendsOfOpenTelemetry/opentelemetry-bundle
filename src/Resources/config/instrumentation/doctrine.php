<?php

use FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Doctrine\Middleware\TraceableMiddleware as TraceableDoctrineMiddleware;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $container->services()
        ->defaults()
        ->private()
        ->set('open_telemetry.instrumentation.doctrine.trace.middleware', TraceableDoctrineMiddleware::class)
            ->arg('$tracer', service('open_telemetry.traces.default_tracer'))
            ->tag('doctrine.middleware')
            ->tag('monolog.logger', ['channel' => 'open_telemetry'])
    ;
};
