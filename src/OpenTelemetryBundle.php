<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle;

use Composer\InstalledVersions;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\Compiler\CachePoolTracingPass;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\Compiler\HttpClientTracingPass;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\Compiler\RemoveConsoleInstrumentationPass;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\Compiler\RemoveDoctrineInstrumentationPass;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\Compiler\RemoveHttpKernelInstrumentationPass;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\Compiler\RemoveMailerInstrumentationPass;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\Compiler\RemoveMessengerInstrumentationPass;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\Compiler\RemoveTwigInstrumentationPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class OpenTelemetryBundle extends Bundle
{
    public static function name(): string
    {
        return 'friendsofopentelemetry/opentelemetry-bundle';
    }

    public static function version(): string
    {
        return InstalledVersions::getPrettyVersion(self::name());
    }

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new CachePoolTracingPass());
        $container->addCompilerPass(new HttpClientTracingPass());
        $container->addCompilerPass(new RemoveConsoleInstrumentationPass());
        $container->addCompilerPass(new RemoveDoctrineInstrumentationPass());
        $container->addCompilerPass(new RemoveHttpKernelInstrumentationPass());
        $container->addCompilerPass(new RemoveMailerInstrumentationPass());
        $container->addCompilerPass(new RemoveMessengerInstrumentationPass());
        $container->addCompilerPass(new RemoveTwigInstrumentationPass());
    }
}
