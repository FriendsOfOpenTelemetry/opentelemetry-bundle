<?php

namespace GaelReyrol\OpenTelemetryBundle\EventSubscriber;

use OpenTelemetry\API\Trace\TracerInterface;
use OpenTelemetry\SDK\Trace\TracerProviderInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;

final class HttpKernelEventSubscriber implements EventSubscriberInterface
{
    private readonly TracerInterface $tracer;

    public function __construct(
        TracerProviderInterface $tracerProvider,
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
