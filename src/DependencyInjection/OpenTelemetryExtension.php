<?php

namespace GaelReyrol\OpenTelemetryBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Console\Application;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;
use Symfony\Component\HttpKernel\HttpKernel;

/**
 * @phpstan-type ComponentInstrumentationOptions array{enabled: bool, provider: string}
 */
final class OpenTelemetryExtension extends ConfigurableExtension
{
    /** @phpstan-ignore-next-line */
    protected function loadInternal(array $mergedConfig, ContainerBuilder $container): void
    {
        $loader = new PhpFileLoader($container, new FileLocator(dirname(__DIR__).'/Resources/config'));
        $loader->load('services.php');

        $this->loadHttpKernelInstrumentation($mergedConfig['instrumentation']['kernel'], $container);
        $this->loadConsoleInstrumentation($mergedConfig['instrumentation']['console'], $container);
    }

    /**
     * @phpstan-param ComponentInstrumentationOptions $config
     */
    private function loadHttpKernelInstrumentation(array $config, ContainerBuilder $container): void
    {
        if (!class_exists(HttpKernel::class)) {
            throw new \LogicException('To configure the HttpKernel instrumentation, you must first install the symfony/http-kernel package.');
        }

        if (true === $config['enabled']) {
            $container->getDefinition('open_telemetry.instrumentation.http_kernel.event_subscriber')->addTag('kernel.event_subscriber');
        }
    }

    /**
     * @phpstan-param ComponentInstrumentationOptions $config
     */
    private function loadConsoleInstrumentation(array $config, ContainerBuilder $container): void
    {
        if (!class_exists(Application::class)) {
            throw new \LogicException('To configure the Console instrumentation, you must first install the symfony/console package.');
        }

        if (true === $config['enabled']) {
            $container->getDefinition('open_telemetry.instrumentation.console.event_subscriber')->addTag('kernel.event_subscriber');
        }
    }
}
