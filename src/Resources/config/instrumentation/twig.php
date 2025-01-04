<?php

use FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Twig\TraceableTwigExtension;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $container->services()
        ->defaults()
        ->private()
        ->set('open_telemetry.instrumentation.twig.trace.extension', TraceableTwigExtension::class)
            ->arg('$tracer', service('open_telemetry.traces.default_tracer'))
            ->tag('twig.extension')
            ->tag('monolog.logger', ['channel' => 'open_telemetry'])
    ;
};
