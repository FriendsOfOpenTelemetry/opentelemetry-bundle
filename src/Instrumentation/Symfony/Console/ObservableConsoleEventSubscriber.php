<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Symfony\Console;

use OpenTelemetry\SDK\Metrics\MeterProviderInterface;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class ObservableConsoleEventSubscriber implements EventSubscriberInterface
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
            ConsoleEvents::TERMINATE => [
                ['flush', -10000],
            ],
        ];
    }

    public function flush(ConsoleTerminateEvent $event): void
    {
        foreach ($this->locator as $provider) {
            $provider->shutdown();
        }
    }
}
