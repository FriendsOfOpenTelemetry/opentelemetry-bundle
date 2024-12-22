<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Exporter\ExporterOptionsInterface;
use Monolog\Level;
use OpenTelemetry\Contrib\Logs\Monolog\Handler;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
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
     *     monolog: array<string, mixed>,
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

        $this->loadMonologHandlers();
    }

    /**
     * @param array{
     *      dsn: string,
     *      options?: ExporterOptions
     *  } $config
     */
    private function loadLogExporter(string $name, array $config): void
    {
        $dsn = (new ChildDefinition('open_telemetry.exporter_dsn'))->setArguments([$config['dsn']]);
        $exporterOptions = (new ChildDefinition('open_telemetry.otlp_exporter_options'))->setArguments([$config['options'] ?? []]);

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
     *  } $config
     */
    private function loadLogProcessor(string $name, array $config): void
    {
        $this->container
            ->setDefinition(
                sprintf('open_telemetry.logs.processors.%s', $name),
                new ChildDefinition('open_telemetry.logs.processor_interface')
            )
            ->setFactory([new Reference(sprintf('open_telemetry.logs.processor_factory.%s', $config['type'])), 'createProcessor'])
            ->setArguments([
                array_map(fn (string $processor) => new Reference($processor), $config['processors'] ?? []),
                isset($config['exporter']) ? new Reference($config['exporter']) : null,
            ]);
    }

    /**
     * @param array{
     *     type: string,
     *     processor?: string,
     * } $config
     */
    private function loadLogProvider(string $name, array $config): void
    {
        $this->container
            ->setDefinition(
                sprintf('open_telemetry.logs.providers.%s', $name),
                new ChildDefinition('open_telemetry.logs.provider_interface')
            )
            ->setFactory([new Reference(sprintf('open_telemetry.logs.provider_factory.%s', $config['type'])), 'createProvider'])
            ->setArguments([
                isset($config['processor']) ? new Reference($config['processor']) : null,
                new Reference('open_telemetry.resource_info'),
            ]);
    }

    /**
     * @param array{
     *     provider: string,
     *     name?: string,
     *     version?: string,
     * } $config
     */
    private function loadLogLogger(string $name, array $config): void
    {
        $this->container
            ->setDefinition(
                sprintf('open_telemetry.logs.loggers.%s', $name),
                new ChildDefinition('open_telemetry.logs.logger_interface'),
            )
            ->setPublic(true)
            ->setFactory([new Reference($config['provider']), 'getLogger'])
            ->setArguments([
                $config['name'] ?? $this->container->getParameter('open_telemetry.bundle.name'),
                $config['version'] ?? $this->container->getParameter('open_telemetry.bundle.version'),
            ]);
    }

    private function loadMonologHandlers(): void
    {
        if (false === $this->config['monolog']['enabled']) {
            return;
        }

        if (!class_exists(Handler::class)) {
            throw new \LogicException('To configure the Monolog handler, you must first install the open-telemetry/opentelemetry-logger-monolog package.');
        }

        foreach ($this->config['monolog']['handlers'] as $name => $handler) {
            $handlerId = sprintf('open_telemetry.logs.monolog.handlers.%s', $name);
            $this->container
                ->setDefinition($handlerId, new ChildDefinition('open_telemetry.logs.monolog.handler'))
                ->setPublic(true)
                ->setArguments([
                    '$loggerProvider' => new Reference($handler['provider']),
                    '$level' => Level::fromName(ucfirst($handler['level'])),
                    '$bubble' => $handler['bubble'],
                ]);
        }
    }
}
