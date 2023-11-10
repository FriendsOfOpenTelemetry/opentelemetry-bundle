<?php

namespace GaelReyrol\OpenTelemetryBundle\EventSubscriber;

use OpenTelemetry\API\Trace\TracerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;

final class HttpKernelEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly TracerInterface $tracer,
    ) {
    }

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
