<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Unit\DependencyInjection;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\OpenTelemetryExtension;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\OpenTelemetryTracesExtension;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\SamplerFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\SpanExporter\AbstractSpanExporterFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\SpanExporter\ConsoleSpanExporterFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\SpanExporter\InMemorySpanExporterFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\SpanExporter\OtlpSpanExporterFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\SpanExporter\SpanExporterFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\SpanExporter\ZipkinSpanExporterFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\SpanProcessor\AbstractSpanProcessorFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\SpanProcessor\MultiSpanProcessorFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\SpanProcessor\NoopSpanProcessorFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\SpanProcessor\SimpleSpanProcessorFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\TracerProvider\AbstractTracerProviderFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\TracerProvider\DefaultTracerProviderFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\TracerProvider\NoopTracerProviderFactory;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use OpenTelemetry\API\Trace\TracerInterface;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;
use OpenTelemetry\SDK\Trace\SpanProcessorInterface;
use OpenTelemetry\SDK\Trace\TracerProviderInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\DependencyInjection\Argument\TaggedIteratorArgument;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Reference;

#[CoversClass(OpenTelemetryTracesExtension::class)]
class OpenTelemetryTracesExtensionTest extends AbstractExtensionTestCase
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

    public function testSamplerFactoryService(): void
    {
        $this->load();

        self::assertContainerBuilderHasService('open_telemetry.traces.sampler_factory', SamplerFactory::class);
        $exporterOptions = $this->container->getDefinition('open_telemetry.traces.sampler_factory');
        self::assertEquals([SamplerFactory::class, 'create'], $exporterOptions->getFactory());
    }

    public function testExporterServices(): void
    {
        $this->load();

        self::assertContainerBuilderHasService('open_telemetry.traces.exporter_factory.abstract', AbstractSpanExporterFactory::class);
        self::assertContainerBuilderHasServiceDefinitionWithArgument('open_telemetry.traces.exporter_factory.abstract', 0, new Reference('open_telemetry.transport_factory'));
        self::assertContainerBuilderHasServiceDefinitionWithArgument('open_telemetry.traces.exporter_factory.abstract', 1, new Reference('logger', ContainerInterface::IGNORE_ON_INVALID_REFERENCE));
        self::assertContainerBuilderHasServiceDefinitionWithTag('open_telemetry.traces.exporter_factory.abstract', 'monolog.logger', ['channel' => 'open_telemetry']);
        $abstractFactory = $this->container->getDefinition('open_telemetry.traces.exporter_factory.abstract');
        self::assertTrue($abstractFactory->isAbstract());

        $exporterFactories = [
            'console' => ConsoleSpanExporterFactory::class,
            'in-memory' => InMemorySpanExporterFactory::class,
            'otlp' => OtlpSpanExporterFactory::class,
            'zipkin' => ZipkinSpanExporterFactory::class,
        ];

        foreach ($exporterFactories as $name => $factory) {
            self::assertContainerBuilderHasService(sprintf('open_telemetry.traces.exporter_factory.%s', $name), $factory);
            self::assertContainerBuilderHasServiceDefinitionWithParent(sprintf('open_telemetry.traces.exporter_factory.%s', $name), 'open_telemetry.traces.exporter_factory.abstract');
        }

        self::assertContainerBuilderHasService('open_telemetry.traces.exporter_factory', SpanExporterFactory::class);
        self::assertContainerBuilderHasServiceDefinitionWithArgument('open_telemetry.traces.exporter_factory', 0, new TaggedIteratorArgument('open_telemetry.traces.exporter_factory'));

        self::assertContainerBuilderHasService('open_telemetry.traces.exporter_interface', SpanExporterInterface::class);
        $exporterInterface = $this->container->getDefinition('open_telemetry.traces.exporter_interface');
        self::assertEquals([new Reference('open_telemetry.traces.exporter_factory'), 'createExporter'], $exporterInterface->getFactory());
    }

    public function testProcessorServices(): void
    {
        $this->load();

        self::assertContainerBuilderHasService('open_telemetry.traces.processor_factory.abstract', AbstractSpanProcessorFactory::class);
        self::assertContainerBuilderHasServiceDefinitionWithArgument('open_telemetry.traces.processor_factory.abstract', 0, new Reference('logger', ContainerInterface::IGNORE_ON_INVALID_REFERENCE));
        self::assertContainerBuilderHasServiceDefinitionWithTag('open_telemetry.traces.processor_factory.abstract', 'monolog.logger', ['channel' => 'open_telemetry']);
        $abstractFactory = $this->container->getDefinition('open_telemetry.traces.processor_factory.abstract');
        self::assertTrue($abstractFactory->isAbstract());

        $processorFactories = [
            'multi' => MultiSpanProcessorFactory::class,
            'noop' => NoopSpanProcessorFactory::class,
            'simple' => SimpleSpanProcessorFactory::class,
        ];

        foreach ($processorFactories as $key => $factory) {
            self::assertContainerBuilderHasService(sprintf('open_telemetry.traces.processor_factory.%s', $key), $factory);
            self::assertContainerBuilderHasServiceDefinitionWithParent(sprintf('open_telemetry.traces.processor_factory.%s', $key), 'open_telemetry.traces.processor_factory.abstract');
        }

        self::assertContainerBuilderHasService('open_telemetry.traces.processor_interface', SpanProcessorInterface::class);
    }

    public function testProviderServices(): void
    {
        $this->load();

        self::assertContainerBuilderHasService('open_telemetry.traces.provider_factory.abstract', AbstractTracerProviderFactory::class);
        self::assertContainerBuilderHasServiceDefinitionWithArgument('open_telemetry.traces.provider_factory.abstract', 0, new Reference('logger', ContainerInterface::IGNORE_ON_INVALID_REFERENCE));
        self::assertContainerBuilderHasServiceDefinitionWithTag('open_telemetry.traces.provider_factory.abstract', 'monolog.logger', ['channel' => 'open_telemetry']);
        $abstractFactory = $this->container->getDefinition('open_telemetry.traces.provider_factory.abstract');
        self::assertTrue($abstractFactory->isAbstract());

        $providerFactories = [
            'noop' => NoopTracerProviderFactory::class,
            'default' => DefaultTracerProviderFactory::class,
        ];

        foreach ($providerFactories as $key => $factory) {
            self::assertContainerBuilderHasService(sprintf('open_telemetry.traces.provider_factory.%s', $key), $factory);
            self::assertContainerBuilderHasServiceDefinitionWithParent(sprintf('open_telemetry.traces.provider_factory.%s', $key), 'open_telemetry.traces.provider_factory.abstract');
        }

        self::assertContainerBuilderHasService('open_telemetry.traces.provider_interface', TracerProviderInterface::class);
    }

    public function testTracerServices(): void
    {
        $this->load();

        self::assertContainerBuilderHasService('open_telemetry.traces.tracer_interface', TracerInterface::class);
    }

    /**
     * @param ?array<string, mixed> $options
     */
    #[DataProvider('exporters')]
    public function testExporters(string $dsn, ?array $options): void
    {
        if (null === $options) {
            $options = [
                'format' => 'json',
                'compression' => 'none',
                'timeout' => 0.3,
                'retry' => 300,
                'max' => 3,
                'headers' => [],
            ];
        }

        $this->load([
            'traces' => [
                'exporters' => [
                    'main' => [
                        'dsn' => $dsn,
                        'options' => $options,
                    ],
                ],
            ],
        ]);

        self::assertTrue($this->container->hasDefinition('open_telemetry.traces.exporters.main'));
        self::assertContainerBuilderHasServiceDefinitionWithParent('open_telemetry.traces.exporters.main', 'open_telemetry.traces.exporter_interface');
        self::assertContainerBuilderHasServiceDefinitionWithArgument(
            'open_telemetry.traces.exporters.main',
            0,
            (new ChildDefinition('open_telemetry.exporter_dsn'))->setArguments([$dsn]),
        );
        self::assertContainerBuilderHasServiceDefinitionWithArgument(
            'open_telemetry.traces.exporters.main',
            1,
            (new ChildDefinition('open_telemetry.otlp_exporter_options'))->setArguments([$options]),
        );
    }

    /**
     * @return \Generator<string, array{
     *     dsn: string,
     *     options: ?array<string, mixed>
     * }>
     */
    public static function exporters(): \Generator
    {
        yield 'default' => [
            'dsn' => 'http+otlp://default',
            'options' => null,
        ];

        yield 'with_options' => [
            'dsn' => 'http+grpc://default',
            'options' => [
                'format' => 'json',
                'compression' => 'gzip',
                'headers' => [
                    'X-Foo' => 'Bar',
                ],
                'timeout' => 0.4,
                'retry' => 500,
                'max' => 5,
                'ca' => 'CA',
                'cert' => 'CERT',
                'key' => 'KEY',
            ],
        ];
    }

    /**
     * @param ?string[] $processors
     */
    #[DataProvider('processors')]
    public function testProcessors(string $type, ?array $processors, ?string $exporter): void
    {
        $processorConfig = [
            'type' => $type,
        ];
        if (null !== $processors) {
            $processorConfig['processors'] = $processors;
        }
        if (null !== $exporter) {
            $processorConfig['exporter'] = $exporter;
        }

        $this->load([
            'traces' => [
                'processors' => [
                    'main' => $processorConfig,
                ],
            ],
        ]);

        self::assertTrue($this->container->hasDefinition('open_telemetry.traces.processors.main'));
        self::assertContainerBuilderHasServiceDefinitionWithParent('open_telemetry.traces.processors.main', 'open_telemetry.traces.processor_interface');
        self::assertContainerBuilderHasServiceDefinitionWithArgument(
            'open_telemetry.traces.processors.main',
            0,
            array_map(fn (string $processor) => new Reference($processor), $processors ?? []),
        );
        self::assertContainerBuilderHasServiceDefinitionWithArgument(
            'open_telemetry.traces.processors.main',
            1,
            null !== $exporter ? new Reference($exporter) : null,
        );
        $processor = $this->container->getDefinition('open_telemetry.traces.processors.main');
        self::assertEquals([new Reference(sprintf('open_telemetry.traces.processor_factory.%s', $type)), 'createProcessor'], $processor->getFactory());
    }

    /**
     * @return \Generator<string, array{
     *     type: string,
     *     processors: ?string[],
     *     exporter: ?string,
     * }>
     */
    public static function processors(): \Generator
    {
        yield 'simple' => [
            'type' => 'simple',
            'processors' => null,
            'exporter' => 'open_telemetry.traces.exporters.default',
        ];

        yield 'noop' => [
            'type' => 'noop',
            'processors' => null,
            'exporter' => null,
        ];

        yield 'multi' => [
            'type' => 'multi',
            'processors' => [
                'open_telemetry.traces.processors.simple',
                'open_telemetry.traces.processors.batch',
            ],
            'exporter' => null,
        ];
    }

    /**
     * @param ?array{
     *     type: string,
     *     service_id?: string,
     *     options?: array<int, mixed>,
     * } $sampler
     * @param ?string[] $processors
     */
    #[DataProvider('providers')]
    public function testProviders(string $type, ?array $sampler, ?array $processors): void
    {
        $providerConfig = [
            'type' => $type,
        ];
        if (null !== $sampler) {
            $providerConfig['sampler'] = [
                'type' => $sampler['type'],
            ];
            if (isset($sampler['options'])) {
                $providerConfig['sampler']['options'] = $sampler['options'];
            }
            if (isset($sampler['service_id'])) {
                $providerConfig['sampler']['service_id'] = $sampler['service_id'];
            }
        }
        if (null !== $processors) {
            $providerConfig['processors'] = $processors;
        }

        $this->load([
            'traces' => [
                'providers' => [
                    'main' => $providerConfig,
                ],
            ],
        ]);

        self::assertTrue($this->container->hasDefinition('open_telemetry.traces.providers.main'));
        self::assertContainerBuilderHasServiceDefinitionWithParent('open_telemetry.traces.providers.main', 'open_telemetry.traces.provider_interface');

        $samplerArg = [
            'always_on',
            [],
        ];
        if (null !== $sampler) {
            $samplerArg = [
                $sampler['type'],
                $sampler['options'] ?? [],
            ];
            if (array_key_exists('service_id', $sampler)) {
                $samplerArg[1]['service_id'] = new Reference($sampler['service_id']);
            }
        }
        self::assertContainerBuilderHasServiceDefinitionWithArgument(
            'open_telemetry.traces.providers.main',
            0,
            (new ChildDefinition('open_telemetry.traces.sampler_factory'))->setArguments($samplerArg),
        );
        self::assertContainerBuilderHasServiceDefinitionWithArgument(
            'open_telemetry.traces.providers.main',
            1,
            null !== $processors ? array_map(fn (string $processor) => new Reference($processor), $processors) : [],
        );
        self::assertContainerBuilderHasServiceDefinitionWithArgument(
            'open_telemetry.traces.providers.main',
            2,
            new Reference('open_telemetry.resource_info'),
        );
        $provider = $this->container->getDefinition('open_telemetry.traces.providers.main');
        self::assertEquals([new Reference(sprintf('open_telemetry.traces.provider_factory.%s', $type)), 'createProvider'], $provider->getFactory());
    }

    /**
     * @return \Generator<string, array{
     *     type: string,
     *     sampler: ?array{
     *         type: string,
     *         service_id?: string,
     *         options?: array<int, mixed>,
     *     },
     *     processors: ?string[],
     * }>
     */
    public static function providers(): \Generator
    {
        yield 'default' => [
            'type' => 'default',
            'sampler' => [
                'type' => 'always_on',
            ],
            'processors' => ['open_telemetry.traces.processors.default'],
        ];

        yield 'ratio' => [
            'type' => 'default',
            'sampler' => [
                'type' => 'trace_id_ratio',
                'options' => [0.2],
            ],
            'processors' => ['open_telemetry.traces.processors.default'],
        ];

        yield 'noop' => [
            'type' => 'noop',
            'sampler' => null,
            'processors' => null,
        ];

        yield 'sampler service' => [
            'type' => 'default',
            'sampler' => [
                'type' => 'service',
                'service_id' => 'my_sampler',
            ],
            'processors' => ['open_telemetry.traces.processors.default'],
        ];
    }

    #[DataProvider('tracers')]
    public function testTracer(string $provider, ?string $name, ?string $version): void
    {
        $tracerConfig = [
            'provider' => $provider,
        ];
        if (null !== $name) {
            $tracerConfig['name'] = $name;
        }
        if (null !== $version) {
            $tracerConfig['version'] = $version;
        }

        $this->load([
            'traces' => [
                'tracers' => [
                    'main' => $tracerConfig,
                ],
            ],
        ]);

        self::assertTrue($this->container->hasDefinition('open_telemetry.traces.tracers.main'));
        self::assertContainerBuilderHasServiceDefinitionWithParent('open_telemetry.traces.tracers.main', 'open_telemetry.traces.tracer_interface');
        self::assertContainerBuilderHasServiceDefinitionWithArgument(
            'open_telemetry.traces.tracers.main',
            0,
            $name ?? $this->container->getParameter('open_telemetry.bundle.name'),
        );
        self::assertContainerBuilderHasServiceDefinitionWithArgument(
            'open_telemetry.traces.tracers.main',
            1,
            $version ?? $this->container->getParameter('open_telemetry.bundle.version'),
        );
        $logger = $this->container->getDefinition('open_telemetry.traces.tracers.main');
        self::assertEquals([new Reference($provider), 'getTracer'], $logger->getFactory());
    }

    /**
     * @return \Generator<string, array{
     *     provider: string,
     *     name: ?string,
     *     version: ?string,
     * }>
     */
    public static function tracers(): \Generator
    {
        yield 'default' => [
            'provider' => 'open_telemetry.traces.providers.main',
            'name' => null,
            'version' => null,
        ];

        yield 'options' => [
            'provider' => 'open_telemetry.traces.providers.main',
            'name' => 'test',
            'version' => 'test',
        ];
    }

    public function testDefaultTracer(): void
    {
        $this->load([
            'traces' => [
                'tracers' => [
                    'main' => [
                        'provider' => 'open_telemetry.traces.providers.main',
                    ],
                    'extra' => [
                        'provider' => 'open_telemetry.traces.providers.extra',
                    ],
                ],
            ],
        ]);

        self::assertContainerBuilderHasAlias('open_telemetry.traces.default_tracer', 'open_telemetry.traces.tracers.main');
        self::assertContainerBuilderHasAlias(TracerInterface::class, 'open_telemetry.traces.tracers.main');
    }

    public function testNoDefaultTracer(): void
    {
        $this->load([
            'traces' => [],
        ]);

        self::assertFalse($this->container->hasAlias('open_telemetry.traces.default_tracer'));
        self::assertFalse($this->container->hasAlias(TracerInterface::class));
    }
}
