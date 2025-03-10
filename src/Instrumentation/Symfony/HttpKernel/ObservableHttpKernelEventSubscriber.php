<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Symfony\HttpKernel;

use OpenTelemetry\SDK\Metrics\MeterProviderInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class ObservableHttpKernelEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        /**
         * @var list<MeterProviderInterface>
         */
        private readonly iterable $locator,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::TERMINATE => [
                ['flush', 10000],
            ],
        ];
    }

    public function flush(TerminateEvent $event): void
    {
        foreach ($this->locator as $provider) {
            $provider->shutdown();
        }
    }
}
