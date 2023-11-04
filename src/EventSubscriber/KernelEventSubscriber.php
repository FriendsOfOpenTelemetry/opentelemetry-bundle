<?php

namespace GaelReyrol\OpenTelemetryBundle\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;

final class KernelEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [],
            KernelEvents::CONTROLLER => [],
            KernelEvents::CONTROLLER_ARGUMENTS => [],
            KernelEvents::VIEW => [],
            KernelEvents::RESPONSE => [],
            KernelEvents::FINISH_REQUEST => [],
            KernelEvents::TERMINATE => [],
            KernelEvents::EXCEPTION => [],
        ];
    }
}
