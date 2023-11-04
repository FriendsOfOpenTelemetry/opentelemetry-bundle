<?php

namespace GaelReyrol\OpenTelemetryBundle\EventSubscriber;

use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class ConsoleEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            ConsoleEvents::COMMAND => [],
            ConsoleEvents::ERROR => [],
            ConsoleEvents::TERMINATE => [],
            ConsoleEvents::SIGNAL => [],
        ];
    }
}
