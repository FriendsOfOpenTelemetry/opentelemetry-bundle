<?php

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Transport\AbstractTransportFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Transport\GrpcTransportFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Transport\KafkaTransportFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Transport\OtlpHttpTransportFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Transport\PsrHttpTransportFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Transport\StreamTransportFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Transport\TransportFactory;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

return static function (ContainerConfigurator $container): void {
    $container->services()
        ->defaults()
        ->private()

        ->set('open_telemetry.transport_factory.abstract', AbstractTransportFactory::class)
            ->abstract()
            ->args([
                service('logger')->ignoreOnInvalid(),
            ])
            ->tag('monolog.logger', ['channel' => 'open_telemetry'])

        ->set('open_telemetry.transport_factory.grpc', GrpcTransportFactory::class)
            ->parent('open_telemetry.transport_factory.abstract')
            ->tag('open_telemetry.transport_factory')

        ->set('open_telemetry.transport_factory.otlp_http', OtlpHttpTransportFactory::class)
            ->parent('open_telemetry.transport_factory.abstract')
            ->tag('open_telemetry.transport_factory')

        ->set('open_telemetry.transport_factory.psr_http', PsrHttpTransportFactory::class)
            ->parent('open_telemetry.transport_factory.abstract')
            ->tag('open_telemetry.transport_factory')

        ->set('open_telemetry.transport_factory.stream', StreamTransportFactory::class)
            ->parent('open_telemetry.transport_factory.abstract')
            ->tag('open_telemetry.transport_factory')

        ->set('open_telemetry.transport_factory.kafka', KafkaTransportFactory::class)
        ->tag('open_telemetry.transport_factory')

        ->set('open_telemetry.transport_factory', TransportFactory::class)
            ->args([
                tagged_iterator('open_telemetry.transport_factory'),
            ])
    ;
};
