<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle;

use Composer\InstalledVersions;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\Compiler\CacheInstrumentationPass;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\Compiler\HttpClientInstrumentationPass;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\Compiler\SetConsoleTracingExcludeCommandsPass;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\Compiler\SetHttpKernelTracingExcludePathsPass;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\Compiler\SetInstrumentationTypePass;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\Compiler\TracerLocatorPass;
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

        $container->addCompilerPass(new SetInstrumentationTypePass());

        $container->addCompilerPass(new CacheInstrumentationPass());
        $container->addCompilerPass(new HttpClientInstrumentationPass());
        $container->addCompilerPass(new SetHttpKernelTracingExcludePathsPass());
        $container->addCompilerPass(new SetConsoleTracingExcludeCommandsPass());

        $container->addCompilerPass(new TracerLocatorPass());
    }
}
