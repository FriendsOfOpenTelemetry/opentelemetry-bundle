<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Unit\DependencyInjection;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\OpenTelemetryExtension;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\OpenTelemetryMetricsExtension;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\ExemplarFilterFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MeterProvider\AbstractMeterProviderFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MeterProvider\DefaultMeterProviderFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MeterProvider\NoopMeterProviderFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MetricExporter\AbstractMetricExporterFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MetricExporter\ConsoleMetricExporterFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MetricExporter\InMemoryMetricExporterFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MetricExporter\MetricExporterFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MetricExporter\NoopMetricExporterFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MetricExporter\OtlpMetricExporterFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\MetricExporterOptions;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use OpenTelemetry\API\Metrics\MeterInterface;
use OpenTelemetry\SDK\Metrics\MeterProviderInterface;
use OpenTelemetry\SDK\Metrics\MetricExporterInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\DependencyInjection\Argument\TaggedIteratorArgument;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Reference;

#[CoversClass(OpenTelemetryMetricsExtension::class)]
class OpenTelemetryMetricsExtensionTest extends AbstractExtensionTestCase
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

    public function testExporterOptionsService(): void
    {
        $this->load();

        self::assertContainerBuilderHasService('open_telemetry.metric_exporter_options', MetricExporterOptions::class);
        $exporterOptions = $this->container->getDefinition('open_telemetry.metric_exporter_options');
        self::assertEquals([MetricExporterOptions::class, 'fromConfiguration'], $exporterOptions->getFactory());
    }

    public function testExemplarFilterFactoryService(): void
    {
        $this->load();

        self::assertContainerBuilderHasService('open_telemetry.metrics.exemplar_filter_factory', ExemplarFilterFactory::class);
        $exporterOptions = $this->container->getDefinition('open_telemetry.metrics.exemplar_filter_factory');
        self::assertEquals([ExemplarFilterFactory::class, 'create'], $exporterOptions->getFactory());
    }

    public function testExporterServices(): void
    {
        $this->load();

        self::assertContainerBuilderHasService('open_telemetry.metrics.exporter_factory.abstract', AbstractMetricExporterFactory::class);
        self::assertContainerBuilderHasServiceDefinitionWithArgument('open_telemetry.metrics.exporter_factory.abstract', 0, new Reference('open_telemetry.transport_factory'));
        self::assertContainerBuilderHasServiceDefinitionWithArgument('open_telemetry.metrics.exporter_factory.abstract', 1, new Reference('logger', ContainerInterface::IGNORE_ON_INVALID_REFERENCE));
        self::assertContainerBuilderHasServiceDefinitionWithTag('open_telemetry.metrics.exporter_factory.abstract', 'monolog.logger', ['channel' => 'open_telemetry']);
        $abstractFactory = $this->container->getDefinition('open_telemetry.metrics.exporter_factory.abstract');
        self::assertTrue($abstractFactory->isAbstract());

        $exporterFactories = [
            'console' => ConsoleMetricExporterFactory::class,
            'in-memory' => InMemoryMetricExporterFactory::class,
            'noop' => NoopMetricExporterFactory::class,
            'otlp' => OtlpMetricExporterFactory::class,
        ];

        foreach ($exporterFactories as $name => $factory) {
            self::assertContainerBuilderHasService(sprintf('open_telemetry.metrics.exporter_factory.%s', $name), $factory);
            self::assertContainerBuilderHasServiceDefinitionWithParent(sprintf('open_telemetry.metrics.exporter_factory.%s', $name), 'open_telemetry.metrics.exporter_factory.abstract');
        }

        self::assertContainerBuilderHasService('open_telemetry.metrics.exporter_factory', MetricExporterFactory::class);
        self::assertContainerBuilderHasServiceDefinitionWithArgument('open_telemetry.metrics.exporter_factory', 0, new TaggedIteratorArgument('open_telemetry.metrics.exporter_factory'));

        self::assertContainerBuilderHasService('open_telemetry.metrics.exporter_interface', MetricExporterInterface::class);
        $exporterInterface = $this->container->getDefinition('open_telemetry.metrics.exporter_interface');
        self::assertEquals([new Reference('open_telemetry.metrics.exporter_factory'), 'createExporter'], $exporterInterface->getFactory());
    }

    public function testProviderServices(): void
    {
        $this->load();

        self::assertContainerBuilderHasService('open_telemetry.metrics.provider_factory.abstract', AbstractMeterProviderFactory::class);
        self::assertContainerBuilderHasServiceDefinitionWithArgument('open_telemetry.metrics.provider_factory.abstract', 0, new Reference('logger', ContainerInterface::IGNORE_ON_INVALID_REFERENCE));
        self::assertContainerBuilderHasServiceDefinitionWithTag('open_telemetry.metrics.provider_factory.abstract', 'monolog.logger', ['channel' => 'open_telemetry']);
        $abstractFactory = $this->container->getDefinition('open_telemetry.metrics.provider_factory.abstract');
        self::assertTrue($abstractFactory->isAbstract());

        $providerFactories = [
            'noop' => NoopMeterProviderFactory::class,
            'default' => DefaultMeterProviderFactory::class,
        ];

        foreach ($providerFactories as $key => $factory) {
            self::assertContainerBuilderHasService(sprintf('open_telemetry.metrics.provider_factory.%s', $key), $factory);
            self::assertContainerBuilderHasServiceDefinitionWithParent(sprintf('open_telemetry.metrics.provider_factory.%s', $key), 'open_telemetry.metrics.provider_factory.abstract');
        }

        self::assertContainerBuilderHasService('open_telemetry.metrics.provider_interface', MeterProviderInterface::class);
    }

    public function testMeterServices(): void
    {
        $this->load();

        self::assertContainerBuilderHasService('open_telemetry.metrics.meter_interface', MeterInterface::class);
    }

    /**
     * @param ?array<string, mixed> $options
     */
    #[DataProvider('exporters')]
    public function testExporters(string $dsn, string $temporality, ?array $options): void
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
            'metrics' => [
                'exporters' => [
                    'main' => [
                        'dsn' => $dsn,
                        'temporality' => $temporality,
                        'options' => $options,
                    ],
                ],
            ],
        ]);

        self::assertTrue($this->container->hasDefinition('open_telemetry.metrics.exporters.main'));
        self::assertContainerBuilderHasServiceDefinitionWithParent('open_telemetry.metrics.exporters.main', 'open_telemetry.metrics.exporter_interface');
        self::assertContainerBuilderHasServiceDefinitionWithArgument(
            'open_telemetry.metrics.exporters.main',
            0,
            (new ChildDefinition('open_telemetry.exporter_dsn'))->setArguments([$dsn]),
        );
        self::assertContainerBuilderHasServiceDefinitionWithArgument(
            'open_telemetry.metrics.exporters.main',
            1,
            (new ChildDefinition('open_telemetry.metric_exporter_options'))->setArguments([
                [
                    $temporality,
                    ...$options,
                ],
            ]),
        );
    }

    /**
     * @return \Generator<string, array{
     *     dsn: string,
     *     temporality: string,
     *     options: ?array<string, mixed>
     * }>
     */
    public static function exporters(): \Generator
    {
        yield 'default' => [
            'dsn' => 'http+otlp://default',
            'temporality' => 'delta',
            'options' => null,
        ];

        yield 'with_options' => [
            'dsn' => 'http+grpc://default',
            'temporality' => 'cumulative',
            'options' => [
                'format' => 'json',
                'compression' => 'gzip',
                'headers' => [
                    [
                        'name' => 'X-Foo',
                        'value' => 'Bar',
                    ],
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
     * @param array{
     *     type: string,
     *     service_id?: string
     * } $filter
     */
    #[DataProvider('providers')]
    public function testProviders(string $type, ?string $exporter, array $filter): void
    {
        $providerConfig = [
            'type' => $type,
            'filter' => $filter,
        ];
        if (null !== $exporter) {
            $providerConfig['exporter'] = $exporter;
        }

        $this->load([
            'metrics' => [
                'providers' => [
                    'main' => $providerConfig,
                ],
            ],
        ]);

        self::assertTrue($this->container->hasDefinition('open_telemetry.metrics.providers.main'));
        self::assertContainerBuilderHasServiceDefinitionWithParent('open_telemetry.metrics.providers.main', 'open_telemetry.metrics.provider_interface');
        self::assertContainerBuilderHasServiceDefinitionWithArgument(
            'open_telemetry.metrics.providers.main',
            0,
            null !== $exporter ? new Reference($exporter) : null,
        );

        $filterArg = [
            $filter['type'],
            [],
        ];
        if (array_key_exists('service_id', $filter)) {
            $filterArg[1]['service_id'] = new Reference($filter['service_id']);
        }
        self::assertContainerBuilderHasServiceDefinitionWithArgument(
            'open_telemetry.metrics.providers.main',
            1,
            (new ChildDefinition('open_telemetry.metrics.exemplar_filter_factory'))->setArguments($filterArg),
        );
        self::assertContainerBuilderHasServiceDefinitionWithArgument(
            'open_telemetry.metrics.providers.main',
            2,
            new Reference('open_telemetry.resource_info'),
        );
        $provider = $this->container->getDefinition('open_telemetry.metrics.providers.main');
        self::assertEquals([new Reference(sprintf('open_telemetry.metrics.provider_factory.%s', $type)), 'createProvider'], $provider->getFactory());
    }

    /**
     * @return \Generator<string, array{
     *     type: string,
     *     exporter: ?string,
     *     filter: array{type: string, service_id?: string},
     * }>
     */
    public static function providers(): \Generator
    {
        yield 'default' => [
            'type' => 'default',
            'exporter' => 'open_telemetry.metrics.exporters.default',
            'filter' => [
                'type' => 'all',
            ],
        ];

        yield 'noop' => [
            'type' => 'noop',
            'exporter' => null,
            'filter' => [
                'type' => 'none',
            ],
        ];

        yield 'filter service' => [
            'type' => 'default',
            'exporter' => 'open_telemetry.metrics.exporters.default',
            'filter' => [
                'type' => 'service',
                'service_id' => 'my_filter',
            ],
        ];
    }

    #[DataProvider('meters')]
    public function testMeters(string $provider, ?string $name): void
    {
        $meterConfig = [
            'provider' => $provider,
        ];
        if (null !== $name) {
            $meterConfig['name'] = $name;
        }

        $this->load([
            'metrics' => [
                'meters' => [
                    'main' => $meterConfig,
                ],
            ],
        ]);

        self::assertTrue($this->container->hasDefinition('open_telemetry.metrics.meters.main'));
        self::assertContainerBuilderHasServiceDefinitionWithParent('open_telemetry.metrics.meters.main', 'open_telemetry.metrics.meter_interface');
        self::assertContainerBuilderHasServiceDefinitionWithArgument(
            'open_telemetry.metrics.meters.main',
            0,
            $name ?? $this->container->getParameter('open_telemetry.bundle.name'),
        );
        $meter = $this->container->getDefinition('open_telemetry.metrics.meters.main');
        self::assertEquals([new Reference($provider), 'getMeter'], $meter->getFactory());
    }

    /**
     * @return \Generator<string, array{
     *     provider: string,
     *     name: ?string,
     * }>
     */
    public static function meters(): \Generator
    {
        yield 'default' => [
            'provider' => 'open_telemetry.logs.providers.main',
            'name' => null,
        ];

        yield 'options' => [
            'provider' => 'open_telemetry.logs.providers.main',
            'name' => 'test',
        ];
    }

    public function testDefaultMeter(): void
    {
        $this->load([
            'metrics' => [
                'meters' => [
                    'main' => [
                        'provider' => 'open_telemetry.metrics.providers.main',
                    ],
                    'extra' => [
                        'provider' => 'open_telemetry.metrics.providers.extra',
                    ],
                ],
            ],
        ]);

        self::assertContainerBuilderHasAlias('open_telemetry.metrics.default_meter', 'open_telemetry.metrics.meters.main');
        self::assertContainerBuilderHasAlias(MeterInterface::class, 'open_telemetry.metrics.meters.main');
    }

    public function testNoDefaultMeter(): void
    {
        $this->load([
            'metrics' => [],
        ]);

        self::assertFalse($this->container->hasAlias('open_telemetry.metrics.default_logger'));
        self::assertFalse($this->container->hasAlias(MeterInterface::class));
    }
}
