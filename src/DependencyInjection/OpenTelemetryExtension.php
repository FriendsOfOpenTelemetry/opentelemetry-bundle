<?php

namespace GaelReyrol\OpenTelemetryBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

final class OpenTelemetryExtension extends ConfigurableExtension
{
    /** @phpstan-ignore-next-line */
    protected function loadInternal(array $mergedConfig, ContainerBuilder $container): void
    {
        $loader = new PhpFileLoader($container, new FileLocator(dirname(__DIR__).'/Resources/config'));
        $loader->load('services.php');

        $kernelInstrumentation = $mergedConfig['instrumentation']['kernel'];
        if (true === $kernelInstrumentation['enabled']) {
            $container->getDefinition('open_telemetry.instrumentation.kernel.event_subscriber')->addTag('kernel.event_subscriber');
        }

        $consoleInstrumentation = $mergedConfig['instrumentation']['console'];
        if (true === $consoleInstrumentation['enabled']) {
            $container->getDefinition('open_telemetry.instrumentation.console.event_subscriber')->addTag('kernel.event_subscriber');
        }
    }
}
