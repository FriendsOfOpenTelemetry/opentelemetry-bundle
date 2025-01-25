<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Unit\DependencyInjection;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\OpenTelemetryExtension;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\OpenTelemetryLogsExtension;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LogExporter\AbstractLogExporterFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LogExporter\ConsoleLogExporterFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LogExporter\InMemoryLogExporterFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LogExporter\LogExporterFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LogExporter\NoopLogExporterFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LogExporter\OtlpLogExporterFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LoggerProvider\AbstractLoggerProviderFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LoggerProvider\DefaultLoggerProviderFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LoggerProvider\NoopLoggerProviderFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LogProcessor\AbstractLogProcessorFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LogProcessor\BatchLogProcessorFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LogProcessor\MultiLogProcessorFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LogProcessor\NoopLogProcessorFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LogProcessor\SimpleLogProcessorFactory;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Monolog\Level;
use OpenTelemetry\API\Logs\LoggerInterface;
use OpenTelemetry\Contrib\Logs\Monolog\Handler as MonologHandler;
use OpenTelemetry\SDK\Logs\LoggerProviderInterface;
use OpenTelemetry\SDK\Logs\LogRecordExporterInterface;
use OpenTelemetry\SDK\Logs\LogRecordProcessorInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\DependencyInjection\Argument\TaggedIteratorArgument;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Reference;

#[CoversClass(OpenTelemetryLogsExtension::class)]
class OpenTelemetryLogsExtensionTest extends AbstractExtensionTestCase
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

    public function testExporterServices(): void
    {
        $this->load();

        self::assertContainerBuilderHasService('open_telemetry.logs.exporter_factory.abstract', AbstractLogExporterFactory::class);
        self::assertContainerBuilderHasServiceDefinitionWithArgument('open_telemetry.logs.exporter_factory.abstract', 0, new Reference('open_telemetry.transport_factory'));
        self::assertContainerBuilderHasServiceDefinitionWithArgument('open_telemetry.logs.exporter_factory.abstract', 1, new Reference('logger', ContainerInterface::IGNORE_ON_INVALID_REFERENCE));
        self::assertContainerBuilderHasServiceDefinitionWithTag('open_telemetry.logs.exporter_factory.abstract', 'monolog.logger', ['channel' => 'open_telemetry']);
        $abstractFactory = $this->container->getDefinition('open_telemetry.logs.exporter_factory.abstract');
        self::assertTrue($abstractFactory->isAbstract());

        $exporterFactories = [
            'console' => ConsoleLogExporterFactory::class,
            'in-memory' => InMemoryLogExporterFactory::class,
            'noop' => NoopLogExporterFactory::class,
            'otlp' => OtlpLogExporterFactory::class,
        ];

        foreach ($exporterFactories as $name => $factory) {
            self::assertContainerBuilderHasService(sprintf('open_telemetry.logs.exporter_factory.%s', $name), $factory);
            self::assertContainerBuilderHasServiceDefinitionWithParent(sprintf('open_telemetry.logs.exporter_factory.%s', $name), 'open_telemetry.logs.exporter_factory.abstract');
        }

        self::assertContainerBuilderHasService('open_telemetry.logs.exporter_factory', LogExporterFactory::class);
        self::assertContainerBuilderHasServiceDefinitionWithArgument('open_telemetry.logs.exporter_factory', 0, new TaggedIteratorArgument('open_telemetry.logs.exporter_factory'));

        self::assertContainerBuilderHasService('open_telemetry.logs.exporter_interface', LogRecordExporterInterface::class);
        $exporterInterface = $this->container->getDefinition('open_telemetry.logs.exporter_interface');
        self::assertEquals([new Reference('open_telemetry.logs.exporter_factory'), 'createExporter'], $exporterInterface->getFactory());
    }

    public function testProcessorServices(): void
    {
        $this->load();

        self::assertContainerBuilderHasService('open_telemetry.logs.processor_factory.abstract', AbstractLogProcessorFactory::class);
        self::assertContainerBuilderHasServiceDefinitionWithArgument('open_telemetry.logs.processor_factory.abstract', 0, new Reference('logger', ContainerInterface::IGNORE_ON_INVALID_REFERENCE));
        self::assertContainerBuilderHasServiceDefinitionWithTag('open_telemetry.logs.processor_factory.abstract', 'monolog.logger', ['channel' => 'open_telemetry']);
        $abstractFactory = $this->container->getDefinition('open_telemetry.logs.processor_factory.abstract');
        self::assertTrue($abstractFactory->isAbstract());

        $processorFactories = [
            'multi' => MultiLogProcessorFactory::class,
            'noop' => NoopLogProcessorFactory::class,
            'simple' => SimpleLogProcessorFactory::class,
            'batch' => BatchLogProcessorFactory::class,
        ];

        foreach ($processorFactories as $key => $factory) {
            self::assertContainerBuilderHasService(sprintf('open_telemetry.logs.processor_factory.%s', $key), $factory);
            self::assertContainerBuilderHasServiceDefinitionWithParent(sprintf('open_telemetry.logs.processor_factory.%s', $key), 'open_telemetry.logs.processor_factory.abstract');
        }

        self::assertContainerBuilderHasService('open_telemetry.logs.processor_interface', LogRecordProcessorInterface::class);
    }

    public function testProviderServices(): void
    {
        $this->load();

        self::assertContainerBuilderHasService('open_telemetry.logs.provider_factory.abstract', AbstractLoggerProviderFactory::class);
        self::assertContainerBuilderHasServiceDefinitionWithArgument('open_telemetry.logs.provider_factory.abstract', 0, new Reference('logger', ContainerInterface::IGNORE_ON_INVALID_REFERENCE));
        self::assertContainerBuilderHasServiceDefinitionWithTag('open_telemetry.logs.provider_factory.abstract', 'monolog.logger', ['channel' => 'open_telemetry']);
        $abstractFactory = $this->container->getDefinition('open_telemetry.logs.provider_factory.abstract');
        self::assertTrue($abstractFactory->isAbstract());

        $providerFactories = [
            'noop' => NoopLoggerProviderFactory::class,
            'default' => DefaultLoggerProviderFactory::class,
        ];

        foreach ($providerFactories as $key => $factory) {
            self::assertContainerBuilderHasService(sprintf('open_telemetry.logs.provider_factory.%s', $key), $factory);
            self::assertContainerBuilderHasServiceDefinitionWithParent(sprintf('open_telemetry.logs.provider_factory.%s', $key), 'open_telemetry.logs.provider_factory.abstract');
        }

        self::assertContainerBuilderHasService('open_telemetry.logs.provider_interface', LoggerProviderInterface::class);
    }

    public function testLoggerServices(): void
    {
        $this->load();

        self::assertContainerBuilderHasService('open_telemetry.logs.logger_interface', LoggerInterface::class);
    }

    public function testMonologHandler(): void
    {
        $this->load([
            'logs' => [
                'monolog' => [
                    'enabled' => true,
                    'handlers' => [
                        'main' => [
                            'provider' => 'open_telemetry.logs.providers.main',
                        ],
                    ],
                ],
                'providers' => [
                    'main' => [
                        'type' => 'noop',
                    ],
                ],
            ],
        ]);

        self::assertContainerBuilderHasService('open_telemetry.logs.monolog.handler', MonologHandler::class);
        self::assertContainerBuilderHasServiceDefinitionWithArgument('open_telemetry.logs.monolog.handlers.main', '$loggerProvider', new Reference('open_telemetry.logs.providers.main'));
        self::assertContainerBuilderHasServiceDefinitionWithArgument('open_telemetry.logs.monolog.handlers.main', '$level', Level::Debug);
        self::assertContainerBuilderHasServiceDefinitionWithArgument('open_telemetry.logs.monolog.handlers.main', '$bubble', true);
        $monologHandler = $this->container->getDefinition('open_telemetry.logs.monolog.handlers.main');
        self::assertTrue($monologHandler->isPublic());
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
            'logs' => [
                'exporters' => [
                    'main' => [
                        'dsn' => $dsn,
                        'options' => $options,
                    ],
                ],
            ],
        ]);

        self::assertTrue($this->container->hasDefinition('open_telemetry.logs.exporters.main'));
        self::assertContainerBuilderHasServiceDefinitionWithParent('open_telemetry.logs.exporters.main', 'open_telemetry.logs.exporter_interface');
        self::assertContainerBuilderHasServiceDefinitionWithArgument(
            'open_telemetry.logs.exporters.main',
            0,
            (new ChildDefinition('open_telemetry.exporter_dsn'))->setArguments([$dsn]),
        );
        self::assertContainerBuilderHasServiceDefinitionWithArgument(
            'open_telemetry.logs.exporters.main',
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
     * @param ?string[] $processors
     * @param ?array{
     *     clock?: string,
     *     max_queue_size?: int,
     *     schedule_delay?: int,
     *     export_timeout?: int,
     *     max_export_batch_size?: int,
     *     auto_flush?: bool,
     *     meter_provider?: string,
     * } $batch
     */
    #[DataProvider('processors')]
    public function testProcessors(string $type, ?array $processors, ?array $batch, ?string $exporter): void
    {
        $processorConfig = [
            'type' => $type,
        ];
        if (null !== $processors) {
            $processorConfig['processors'] = $processors;
        }
        if (null !== $batch) {
            $processorConfig['batch'] = $batch;
        }
        if (null !== $exporter) {
            $processorConfig['exporter'] = $exporter;
        }

        $this->load([
            'logs' => [
                'processors' => [
                    'main' => $processorConfig,
                ],
            ],
        ]);

        self::assertTrue($this->container->hasDefinition('open_telemetry.logs.processors.main'));
        self::assertContainerBuilderHasServiceDefinitionWithParent('open_telemetry.logs.processors.main', 'open_telemetry.logs.processor_interface');
        self::assertContainerBuilderHasServiceDefinitionWithArgument(
            'open_telemetry.logs.processors.main',
            0,
            array_map(fn (string $processor) => new Reference($processor), $processors ?? []),
        );
        if (null !== $batch) {
            self::assertContainerBuilderHasServiceDefinitionWithArgument(
                'open_telemetry.logs.processors.main',
                1,
                [
                    'clock' => 'open_telemetry.clock',
                    'max_queue_size' => 2048,
                    'schedule_delay' => 1000,
                    'export_timeout' => 30000,
                    'max_export_batch_size' => 512,
                    'auto_flush' => true,
                    'meter_provider' => null,
                ],
            );
        } else {
            self::assertContainerBuilderHasServiceDefinitionWithArgument(
                'open_telemetry.logs.processors.main',
                1,
                null,
            );
        }
        self::assertContainerBuilderHasServiceDefinitionWithArgument(
            'open_telemetry.logs.processors.main',
            2,
            null !== $exporter ? new Reference($exporter) : null,
        );
        $processor = $this->container->getDefinition('open_telemetry.logs.processors.main');
        self::assertEquals([new Reference(sprintf('open_telemetry.logs.processor_factory.%s', $type)), 'createProcessor'], $processor->getFactory());
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
            'batch' => null,
            'exporter' => 'open_telemetry.logs.exporters.default',
        ];

        yield 'noop' => [
            'type' => 'noop',
            'processors' => null,
            'batch' => null,
            'exporter' => null,
        ];

        yield 'multi' => [
            'type' => 'multi',
            'processors' => [
                'open_telemetry.logs.processors.simple',
                'open_telemetry.logs.processors.batch',
            ],
            'batch' => null,
            'exporter' => null,
        ];

        yield 'batch' => [
            'type' => 'batch',
            'processors' => null,
            'batch' => [],
            'exporter' => 'open_telemetry.logs.exporters.default',
        ];
    }

    #[DataProvider('providers')]
    public function testProviders(string $type, ?string $processor): void
    {
        $providerConfig = [
            'type' => $type,
        ];
        if (null !== $processor) {
            $providerConfig['processor'] = $processor;
        }

        $this->load([
            'logs' => [
                'providers' => [
                    'main' => $providerConfig,
                ],
            ],
        ]);

        self::assertTrue($this->container->hasDefinition('open_telemetry.logs.providers.main'));
        self::assertContainerBuilderHasServiceDefinitionWithParent('open_telemetry.logs.providers.main', 'open_telemetry.logs.provider_interface');
        self::assertContainerBuilderHasServiceDefinitionWithArgument(
            'open_telemetry.logs.providers.main',
            0,
            null !== $processor ? new Reference($processor) : null,
        );
        self::assertContainerBuilderHasServiceDefinitionWithArgument(
            'open_telemetry.logs.providers.main',
            1,
            new Reference('open_telemetry.resource_info'),
        );
        $provider = $this->container->getDefinition('open_telemetry.logs.providers.main');
        self::assertEquals([new Reference(sprintf('open_telemetry.logs.provider_factory.%s', $type)), 'createProvider'], $provider->getFactory());
    }

    /**
     * @return \Generator<string, array{
     *     type: string,
     *     processor: ?string,
     * }>
     */
    public static function providers(): \Generator
    {
        yield 'default' => [
            'type' => 'default',
            'processor' => 'open_telemetry.logs.processors.default',
        ];

        yield 'noop' => [
            'type' => 'noop',
            'processor' => null,
        ];
    }

    #[DataProvider('loggers')]
    public function testLogger(string $provider, ?string $name, ?string $version): void
    {
        $loggerConfig = [
            'provider' => $provider,
        ];
        if (null !== $name) {
            $loggerConfig['name'] = $name;
        }
        if (null !== $version) {
            $loggerConfig['version'] = $version;
        }

        $this->load([
            'logs' => [
                'loggers' => [
                    'main' => $loggerConfig,
                ],
            ],
        ]);

        self::assertTrue($this->container->hasDefinition('open_telemetry.logs.loggers.main'));
        self::assertContainerBuilderHasServiceDefinitionWithParent('open_telemetry.logs.loggers.main', 'open_telemetry.logs.logger_interface');
        self::assertContainerBuilderHasServiceDefinitionWithArgument(
            'open_telemetry.logs.loggers.main',
            0,
            $name ?? $this->container->getParameter('open_telemetry.bundle.name'),
        );
        self::assertContainerBuilderHasServiceDefinitionWithArgument(
            'open_telemetry.logs.loggers.main',
            1,
            $version ?? $this->container->getParameter('open_telemetry.bundle.version'),
        );
        $logger = $this->container->getDefinition('open_telemetry.logs.loggers.main');
        self::assertEquals([new Reference($provider), 'getLogger'], $logger->getFactory());
    }

    /**
     * @return \Generator<string, array{
     *     provider: string,
     *     name: ?string,
     *     version: ?string,
     * }>
     */
    public static function loggers(): \Generator
    {
        yield 'default' => [
            'provider' => 'open_telemetry.logs.providers.main',
            'name' => null,
            'version' => null,
        ];

        yield 'options' => [
            'provider' => 'open_telemetry.logs.providers.main',
            'name' => 'test',
            'version' => 'test',
        ];
    }

    public function testDefaultLogger(): void
    {
        $this->load([
            'logs' => [
                'loggers' => [
                    'main' => [
                        'provider' => 'open_telemetry.logs.providers.main',
                    ],
                    'extra' => [
                        'provider' => 'open_telemetry.logs.providers.extra',
                    ],
                ],
            ],
        ]);

        self::assertContainerBuilderHasAlias('open_telemetry.logs.default_logger', 'open_telemetry.logs.loggers.main');
    }

    public function testNoDefaultLogger(): void
    {
        $this->load([
            'logs' => [],
        ]);

        self::assertFalse($this->container->hasAlias('open_telemetry.logs.default_logger'));
    }
}
