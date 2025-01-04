<?php

use FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Symfony\Mailer\TraceableMailer;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Symfony\Mailer\TraceableMailerTransport;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $container->services()
        ->defaults()
        ->private()
        ->set('open_telemetry.instrumentation.mailer.trace.transports', TraceableMailerTransport::class)
            ->decorate('mailer.transports')
            ->arg('$tracer', service('open_telemetry.traces.default_tracer'))
            ->arg('$transport', service('.inner'))
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
    ;
};
