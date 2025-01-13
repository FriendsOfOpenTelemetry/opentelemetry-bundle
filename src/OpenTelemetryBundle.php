<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle;

use Composer\InstalledVersions;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\Compiler\CacheInstrumentationPass;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\Compiler\ConsoleInstrumentationPass;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\Compiler\DoctrineInstrumentationPass;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\Compiler\HttpClientInstrumentationPass;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\Compiler\HttpKernelInstrumentationPass;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\Compiler\MailerInstrumentationPass;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\Compiler\MessengerInstrumentationPass;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\Compiler\SetInstrumentationTypePass;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\Compiler\TracerLocatorPass;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\Compiler\TwigInstrumentationPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
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
        $container->addCompilerPass(new ConsoleInstrumentationPass());
        $container->addCompilerPass(new DoctrineInstrumentationPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 10);
        $container->addCompilerPass(new HttpClientInstrumentationPass());
        $container->addCompilerPass(new HttpKernelInstrumentationPass());
        $container->addCompilerPass(new MailerInstrumentationPass());
        $container->addCompilerPass(new MessengerInstrumentationPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 10);
        $container->addCompilerPass(new TwigInstrumentationPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 10);

        $container->addCompilerPass(new TracerLocatorPass());
    }
}
