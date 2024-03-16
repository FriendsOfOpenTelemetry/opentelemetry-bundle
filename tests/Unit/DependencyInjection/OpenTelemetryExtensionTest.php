<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Unit\DependencyInjection;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\OpenTelemetryExtension;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Context\Propagator\HeadersPropagator as HeadersPropagationGetter;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterDsn;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\OtlpExporterOptions;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Transport\AbstractTransportFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Transport\GrpcTransportFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Transport\OtlpHttpTransportFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Transport\PsrHttpTransportFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Transport\StreamTransportFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Transport\TransportFactory;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use OpenTelemetry\Context\Propagation\ArrayAccessGetterSetter;
use OpenTelemetry\Context\Propagation\MultiTextMapPropagator;
use OpenTelemetry\Context\Propagation\NoopTextMapPropagator;
use OpenTelemetry\Context\Propagation\SanitizeCombinedHeadersPropagationGetter;
use OpenTelemetry\Contrib\Propagation\ServerTiming\ServerTimingPropagator;
use OpenTelemetry\Contrib\Propagation\TraceResponse\TraceResponsePropagator;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\DependencyInjection\Argument\TaggedIteratorArgument;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Reference;

#[CoversClass(OpenTelemetryExtension::class)]
class OpenTelemetryExtensionTest extends AbstractExtensionTestCase
{
    protected function getContainerExtensions(): array
    {
        return [new OpenTelemetryExtension()];
    }

    /**
     * @return array{
     *     service: array{
     *      namespace: string,
     *      name: string,
     *      version: string,
     *      environment: string
     *     }
     * }
     */
    protected function getMinimalConfiguration(): array
    {
        return [
            'service' => [
                'namespace' => 'FriendsOfOpenTelemetry/OpenTelemetry',
                'name' => 'Test',
                'version' => '0.0.0',
                'environment' => 'test',
            ],
        ];
    }

    public function testDefaultParams(): void
    {
        $this->load();

        self::assertContainerBuilderHasParameter('open_telemetry.bundle.name', 'friendsofopentelemetry/opentelemetry-bundle');
        self::assertContainerBuilderHasParameter('open_telemetry.bundle.version');
        self::assertContainerBuilderHasParameter('monolog.additional_channels', ['open_telemetry']);
    }

    public function testDefaultServices(): void
    {
        $this->load();

        self::assertContainerBuilderHasService('open_telemetry.propagator.server_timing', ServerTimingPropagator::class);
        self::assertContainerBuilderHasService('open_telemetry.propagator.trace_response', TraceResponsePropagator::class);

        self::assertContainerBuilderHasService('open_telemetry.propagator_text_map.noop', NoopTextMapPropagator::class);
        self::assertContainerBuilderHasService('open_telemetry.propagator_text_map.multi', MultiTextMapPropagator::class);

        self::assertContainerBuilderHasService('open_telemetry.propagation_getter.headers', HeadersPropagationGetter::class);
        self::assertContainerBuilderHasService('open_telemetry.propagation_getter.sanitize_combined_headers', SanitizeCombinedHeadersPropagationGetter::class);

        self::assertContainerBuilderHasService('open_telemetry.propagation_getter_setter.array_access', ArrayAccessGetterSetter::class);

        self::assertContainerBuilderHasService('open_telemetry.exporter_dsn', ExporterDsn::class);
        $exporterDsn = $this->container->getDefinition('open_telemetry.exporter_dsn');
        self::assertSame([ExporterDsn::class, 'fromString'], $exporterDsn->getFactory());

        self::assertContainerBuilderHasService('open_telemetry.otlp_exporter_options', OtlpExporterOptions::class);
        $otlpExporterOptions = $this->container->getDefinition('open_telemetry.otlp_exporter_options');
        self::assertSame([OtlpExporterOptions::class, 'fromConfiguration'], $otlpExporterOptions->getFactory());
    }

    public function testTransports(): void
    {
        $this->load();

        self::assertContainerBuilderHasService('open_telemetry.transport_factory.abstract', AbstractTransportFactory::class);
        $abstractTransport = $this->container->getDefinition('open_telemetry.transport_factory.abstract');
        self::assertTrue($abstractTransport->isAbstract());
        self::assertEquals([new Reference('logger', ContainerInterface::IGNORE_ON_INVALID_REFERENCE)], $abstractTransport->getArguments());
        self::assertSame([['channel' => 'open_telemetry']], $abstractTransport->getTag('monolog.logger'));

        $transportFactories = [
            'grpc' => GrpcTransportFactory::class,
            'otlp_http' => OtlpHttpTransportFactory::class,
            'psr_http' => PsrHttpTransportFactory::class,
            'stream' => StreamTransportFactory::class,
        ];

        foreach ($transportFactories as $name => $class) {
            $transportId = sprintf('open_telemetry.transport_factory.%s', $name);
            self::assertContainerBuilderHasService($transportId, $class);
            self::assertContainerBuilderHasServiceDefinitionWithParent($transportId, 'open_telemetry.transport_factory.abstract');
            self::assertContainerBuilderHasServiceDefinitionWithTag($transportId, 'open_telemetry.transport_factory');
        }

        self::assertContainerBuilderHasService('open_telemetry.transport_factory', TransportFactory::class);
        self::assertContainerBuilderHasServiceDefinitionWithArgument('open_telemetry.transport_factory', 0, new TaggedIteratorArgument('open_telemetry.transport_factory'));
    }

    public function testInstrumentationParams(): void
    {
        $instrumentationConfig = [
            'tracing' => [
                'enabled' => true,
            ],
            'metering' => [
                'enabled' => true,
            ],
        ];

        $config = [
            'instrumentation' => [
                'cache' => $instrumentationConfig,
                'console' => $instrumentationConfig,
                'http_client' => $instrumentationConfig,
                'http_kernel' => $instrumentationConfig,
                'mailer' => $instrumentationConfig,
                'messenger' => $instrumentationConfig,
                'twig' => $instrumentationConfig,
            ],
        ];

        $this->load($config);

        foreach (array_keys($config['instrumentation']) as $name) {
            self::assertContainerBuilderHasParameter(sprintf('open_telemetry.instrumentation.%s.tracing.enabled', $name), true);
            self::assertContainerBuilderHasParameter(sprintf('open_telemetry.instrumentation.%s.tracing.tracer', $name), 'default_tracer');
            self::assertContainerBuilderHasParameter(sprintf('open_telemetry.instrumentation.%s.metering.enabled', $name), true);
            self::assertContainerBuilderHasParameter(sprintf('open_telemetry.instrumentation.%s.metering.meter', $name), 'default_meter');
        }
    }
}
