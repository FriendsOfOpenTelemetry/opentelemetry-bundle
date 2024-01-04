<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\ExtensionLoader;

use Symfony\Component\DependencyInjection\ContainerBuilder;

interface ExtensionLoaderInterface
{
    public function load(array $config, ContainerBuilder $container): void;
}
