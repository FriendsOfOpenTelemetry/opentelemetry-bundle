<?php

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Context\Propagator\HeadersPropagator as HeadersPropagationGetter;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterDsn;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\OtlpExporterOptions;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetryBundle;
use OpenTelemetry\Context\Propagation\ArrayAccessGetterSetter;
use OpenTelemetry\Context\Propagation\MultiTextMapPropagator;
use OpenTelemetry\Context\Propagation\NoopTextMapPropagator;
use OpenTelemetry\Context\Propagation\SanitizeCombinedHeadersPropagationGetter;
use OpenTelemetry\Contrib\Propagation\ServerTiming\ServerTimingPropagator;
use OpenTelemetry\Contrib\Propagation\TraceResponse\TraceResponsePropagator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\abstract_arg;

return static function (ContainerConfigurator $container): void {
    $container->parameters()
        ->set('open_telemetry.bundle.name', OpenTelemetryBundle::name())
        ->set('open_telemetry.bundle.version', OpenTelemetryBundle::version())
        ->set('monolog.additional_channels', ['open_telemetry'])
    ;

    $container->services()
        ->defaults()
        ->private()

        ->set('open_telemetry.propagator.server_timing', ServerTimingPropagator::class)
        ->set('open_telemetry.propagator.trace_response', TraceResponsePropagator::class)

        ->set('open_telemetry.propagator_text_map.noop', NoopTextMapPropagator::class)
        ->set('open_telemetry.propagator_text_map.multi', MultiTextMapPropagator::class)

        ->set('open_telemetry.propagation_getter.headers', HeadersPropagationGetter::class)
        ->set('open_telemetry.propagation_getter.sanitize_combined_headers', SanitizeCombinedHeadersPropagationGetter::class)

        ->set('open_telemetry.propagation_getter_setter.array_access', ArrayAccessGetterSetter::class)

        ->set('open_telemetry.exporter_dsn', OtlpExporterOptions::class)
            ->factory([ExporterDsn::class, 'fromString'])
//            ->args([
//                abstract_arg('dsn'),
//            ])

        ->set('open_telemetry.otlp_exporter_options', OtlpExporterOptions::class)
            ->factory([OtlpExporterOptions::class, 'fromConfiguration'])
//            ->args([
//                abstract_arg('options'),
//            ])
    ;
};
