<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class MailerInstrumentationPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (true === $container->hasParameter('open_telemetry.instrumentation.mailer.tracing.enabled')
            && true === $container->getParameter('open_telemetry.instrumentation.mailer.tracing.enabled')) {
            return;
        }

        $container->removeDefinition('open_telemetry.instrumentation.mailer.trace.transports');
        $container->removeDefinition('open_telemetry.instrumentation.mailer.trace.default_transport');
        $container->removeDefinition('open_telemetry.instrumentation.mailer.trace.mailer');
    }
}
