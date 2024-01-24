<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\ExtensionLoader;

use Symfony\Component\DependencyInjection\ContainerBuilder;

interface ExtensionLoaderInterface
{
    /**
     * @param array<string, mixed> $config
     */
    public function load(array $config, ContainerBuilder $container): void;
}
