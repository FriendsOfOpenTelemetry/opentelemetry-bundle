<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport\TransportFactoryInterface;

class MailerInstrumentationPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (false === $container->hasParameter('open_telemetry.instrumentation.mailer.tracing.enabled')
            || false === $container->getParameter('open_telemetry.instrumentation.mailer.tracing.enabled')) {
            $container->removeDefinition('open_telemetry.instrumentation.mailer.trace.transports');
            $container->removeDefinition('open_telemetry.instrumentation.mailer.trace.default_transport');
            $container->removeDefinition('open_telemetry.instrumentation.mailer.trace.mailer');

            return;
        }

        if (!interface_exists(MailerInterface::class) || !interface_exists(TransportFactoryInterface::class)) {
            throw new \LogicException('Mailer instrumentation cannot be enabled because the symfony/mailer package is not installed.');
        }
    }
}
