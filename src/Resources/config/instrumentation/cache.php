<?php

use FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Symfony\Cache\TagAwareTraceableCacheAdapter;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Symfony\Cache\TraceableCacheAdapter;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $container->services()
        ->defaults()
        ->private()
        ->set('open_telemetry.instrumentation.cache.trace.adapter', TraceableCacheAdapter::class)
            ->arg('$tracer', service('open_telemetry.traces.default_tracer'))
            ->abstract()
            ->tag('monolog.logger', ['channel' => 'open_telemetry'])

        ->set('open_telemetry.instrumentation.cache.trace.tag_aware_adapter', TagAwareTraceableCacheAdapter::class)
            ->arg('$tracer', service('open_telemetry.traces.default_tracer'))
            ->abstract()
            ->tag('monolog.logger', ['channel' => 'open_telemetry'])
    ;
};
