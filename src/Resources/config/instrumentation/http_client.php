<?php

use FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Symfony\HttpClient\TraceableHttpClient;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $container->services()
        ->defaults()
        ->private()
        ->set('open_telemetry.instrumentation.http_client.trace.client', TraceableHttpClient::class)
            ->arg('$tracer', service('open_telemetry.traces.default_tracer'))
            ->tag('monolog.logger', ['channel' => 'open_telemetry'])
    ;
};
