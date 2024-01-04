<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\ExtensionLoader;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\ExporterDefinitionsFactory;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterDsn;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterOptionsInterface;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LogExporter\LogExporterEnum;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LoggerProvider\LoggerProviderEnum;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LoggerProvider\LoggerProviderFactoryInterface;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LogProcessor\LogProcessorEnum;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Log\LogProcessor\LogProcessorFactoryInterface;
use OpenTelemetry\SDK\Logs\LoggerProviderInterface;
use OpenTelemetry\SDK\Logs\LogRecordProcessorInterface;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @phpstan-import-type ExporterOptions from ExporterOptionsInterface
 */
final class LogsExtensionLoader implements ExtensionLoaderInterface
{
    private array $config;
    private ContainerBuilder $container;

    /**
     * @param array{
     *     default_logger?: string,
     *     loggers: array<string, mixed>,
     *     exporters: array<string, mixed>,
     *     processors: array<string, mixed>,
     *     providers: array<string, mixed>
     * } $config
     */
    public function load(array $config, ContainerBuilder $container): void
    {
        $this->config = $config;
        $this->container = $container;

        foreach ($this->config['exporters'] as $name => $exporter) {
            $this->loadLogExporter($name, $exporter);
        }

        foreach ($this->config['processors'] as $name => $processor) {
            $this->loadLogProcessor($name, $processor);
        }

        foreach ($this->config['providers'] as $name => $provider) {
            $this->loadLogProvider($name, $provider);
        }

        foreach ($this->config['loggers'] as $name => $logger) {
            $this->loadLogLogger($name, $logger);
        }

        $defaultLogger = $this->config['default_logger'] ?? null;
        if (0 < count($this->config['loggers'])) {
            $defaultLogger = array_key_first($this->config['loggers']);
        }

        if (null !== $defaultLogger) {
            $this->container->set('open_telemetry.logs.default_logger', new Reference(sprintf('open_telemetry.logs.loggers.%s', $defaultLogger)));
        }
    }

    /**
     * @param array{
     *      dsn: string,
     *      options?: ExporterOptions
     *  } $options
     */
    private function loadLogExporter(string $name, array $options): void
    {
        $exporterId = sprintf('open_telemetry.logs.exporters.%s', $name);
        $dsn = ExporterDsn::fromString($options['dsn']);
        $exporter = LogExporterEnum::from($dsn->getExporter());

        $exporterDefinitionsFactory = new ExporterDefinitionsFactory($this->container);

        $this->container
            ->setDefinition($exporterId, new ChildDefinition('open_telemetry.logs.exporter'))
            ->setClass($exporter->getClass())
            ->setFactory([$exporter->getFactoryClass(), 'createExporter'])
            ->setArguments([
                '$dsn' => $exporterDefinitionsFactory->createExporterDsnDefinition($options['dsn']),
                '$options' => $exporterDefinitionsFactory->createExporterOptionsDefinition($options['options'] ?? []),
            ]);
    }

    /**
     * @param array{
     *      type: string,
     *      processors?: string[],
     *      exporter?: string
     *  } $processor
     */
    private function loadLogProcessor(string $name, array $processor): void
    {
        $processorId = sprintf('open_telemetry.logs.processors.%s', $name);
        $options = $this->getLogProcessorOptions($processor);

        $this->container
            ->setDefinition($processorId, new ChildDefinition('open_telemetry.logs.processor'))
            ->setClass($options['class'])
            ->setFactory([$options['factory'], 'createProcessor'])
            ->setArguments([
                '$processors' => $options['processors'],
                '$exporter' => $options['exporter'],
            ]);
    }

    /**
     * @param array{
     *     type: string,
     *     processors?: string[],
     *     exporter?: string
     * } $processor
     *
     * @return array{
     *     factory: class-string<LogProcessorFactoryInterface>,
     *     class: class-string<LogRecordProcessorInterface>,
     *     processors: ?Reference[],
     *     exporter: ?Reference,
     * }
     */
    private function getLogProcessorOptions(array $processor): array
    {
        $processorEnum = LogProcessorEnum::from($processor['type']);
        $options = [
            'factory' => $processorEnum->getFactoryClass(),
            'class' => $processorEnum->getClass(),
            'processors' => [],
            'exporter' => null,
        ];

        // if (LogProcessorEnum::Batch === $processorEnum) {
        //     // TODO: Check batch options
        //     clock: OpenTelemetry\SDK\Common\Time\SystemClock
        //     max_queue_size: 2048
        //     schedule_delay: 5000
        //     export_timeout: 30000
        //     max_export_batch_size: 512
        //     auto_flush: true
        // }

        if (LogProcessorEnum::Multi === $processorEnum) {
            if (!isset($processor['processors']) || 0 === count($processor['processors'])) {
                throw new \InvalidArgumentException('Processors are not set or empty');
            }
            $options['processors'] = array_map(
                fn (string $processor) => new Reference(sprintf('open_telemetry.logs.processors.%s', $processor)),
                $processor['processors'],
            );
        }

        if (LogProcessorEnum::Simple === $processorEnum) {
            if (!isset($processor['exporter'])) {
                throw new \InvalidArgumentException('Exporter is not set');
            }
            $options['exporter'] = new Reference(sprintf('open_telemetry.logs.exporters.%s', $processor['exporter']));
        }

        return $options;
    }

    /**
     * @param array{
     *     type: string,
     *     processor?: string,
     * } $provider
     */
    private function loadLogProvider(string $name, array $provider): void
    {
        $providerId = sprintf('open_telemetry.logs.providers.%s', $name);
        $options = $this->getLoggerProviderOptions($provider);

        $this->container
            ->setDefinition($providerId, new ChildDefinition('open_telemetry.logs.provider'))
            ->setClass($options['class'])
            ->setFactory([$options['factory'], 'createProvider'])
            ->setArguments([
                '$processor' => $options['processor'],
            ]);
    }

    /**
     * @param array{
     *     type: string,
     *     processor?: string,
     * } $provider
     *
     * @return array{
     *     factory: class-string<LoggerProviderFactoryInterface>,
     *     class: class-string<LoggerProviderInterface>,
     *     processor: ?Reference,
     * }
     */
    private function getLoggerProviderOptions(array $provider): array
    {
        $providerEnum = LoggerProviderEnum::from($provider['type']);
        $options = [
            'factory' => $providerEnum->getFactoryClass(),
            'class' => $providerEnum->getClass(),
            'processor' => null,
        ];

        if (LoggerProviderEnum::Default === $providerEnum) {
            if (!isset($provider['processor'])) {
                throw new \InvalidArgumentException('Processor is not set');
            }
            $options['processor'] = new Reference(sprintf('open_telemetry.logs.processors.%s', $provider['processor']));
        }

        return $options;
    }

    /**
     * @param array{
     *     provider: string,
     *     name?: string,
     *     version?: string,
     * } $logger
     */
    private function loadLogLogger(string $name, array $logger): void
    {
        $loggerId = sprintf('open_telemetry.logs.loggers.%s', $name);

        $this->container
            ->setDefinition($loggerId, new ChildDefinition('open_telemetry.logs.logger'))
            ->setPublic(true)
            ->setFactory([
                new Reference(sprintf('open_telemetry.logs.providers.%s', $logger['provider'])),
                'getLogger',
            ])
            ->setArguments([
                $logger['name'] ?? $this->container->getParameter('open_telemetry.bundle.name'),
                $logger['version'] ?? $this->container->getParameter('open_telemetry.bundle.version'),
            ]);
    }
}
