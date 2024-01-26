<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterOptionsInterface;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @phpstan-import-type ExporterOptions from ExporterOptionsInterface
 */
final class OpenTelemetryLogsExtension
{
    /**
     * @var array<string, mixed>
     */
    private array $config;
    private ContainerBuilder $container;

    /**
     * @param array{
     *     loggers: array<string, mixed>,
     *     exporters: array<string, mixed>,
     *     processors: array<string, mixed>,
     *     providers: array<string, mixed>
     * }|array<string, mixed> $config
     */
    public function __invoke(array $config, ContainerBuilder $container): void
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

        $defaultLogger = null;
        if (0 < count($this->config['loggers'])) {
            $defaultLogger = array_key_first($this->config['loggers']);
        }

        if (null !== $defaultLogger) {
            $this->container->setAlias('open_telemetry.logs.default_logger', new Reference(sprintf('open_telemetry.logs.loggers.%s', $defaultLogger)));
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
        $dsn = $this->container->getDefinition('open_telemetry.exporter_dsn')->setArguments([$options['dsn']]);
        $exporterOptions = $this->container->getDefinition('open_telemetry.otlp_exporter_options')->setArguments([$options['options'] ?? []]);

        $this->container
            ->setDefinition(
                sprintf('open_telemetry.logs.exporters.%s', $name),
                new ChildDefinition('open_telemetry.logs.exporter_interface'),
            )
            ->setArguments([$dsn, $exporterOptions]);
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
        $this->container
            ->setDefinition(
                sprintf('open_telemetry.logs.processors.%s', $name),
                new ChildDefinition('open_telemetry.logs.processor_interface')
            )
            ->setFactory([new Reference(sprintf('open_telemetry.logs.processor_factory.%s', $processor['type'])), 'createProcessor'])
            ->setArguments([
                array_map(fn (string $processor) => new Reference($processor), $processor['processors'] ?? []),
                new Reference($processor['exporter'], ContainerInterface::NULL_ON_INVALID_REFERENCE),
            ]);
    }

    /**
     * @param array{
     *     type: string,
     *     processor?: string,
     * } $provider
     */
    private function loadLogProvider(string $name, array $provider): void
    {
        $this->container
            ->setDefinition(
                sprintf('open_telemetry.logs.providers.%s', $name),
                new ChildDefinition('open_telemetry.logs.provider_interface')
            )
            ->setFactory([new Reference(sprintf('open_telemetry.logs.provider_factory.%s', $provider['type'])), 'createProvider'])
            ->setArguments([
                new Reference($provider['processor'] ?? ''),
            ]);
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
        $this->container
            ->setDefinition(
                sprintf('open_telemetry.logs.loggers.%s', $name),
                new ChildDefinition('open_telemetry.logs.logger'),
            )
            ->setPublic(true)
            ->setFactory([new Reference($logger['provider']), 'getLogger'])
            ->setArguments([
                $logger['name'] ?? $this->container->getParameter('open_telemetry.bundle.name'),
                $logger['version'] ?? $this->container->getParameter('open_telemetry.bundle.version'),
            ]);
    }
}
