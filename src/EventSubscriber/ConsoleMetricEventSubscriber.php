<?php

namespace GaelReyrol\OpenTelemetryBundle\EventSubscriber;

use OpenTelemetry\API\Metrics\MeterInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Event\ConsoleErrorEvent;
use Symfony\Component\Console\Event\ConsoleSignalEvent;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class ConsoleMetricEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly MeterInterface $meter,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ConsoleEvents::COMMAND => [
                ['recordCommand', 10000],
            ],
            ConsoleEvents::ERROR => [
                ['recordError', -10000],
            ],
            ConsoleEvents::TERMINATE => [
                ['recordStatus', -10000],
            ],
            ConsoleEvents::SIGNAL => [
                ['recordSignal', -10000],
            ],
        ];
    }

    public function recordCommand(ConsoleCommandEvent $event): void
    {
        $command = $event->getCommand();

        assert($command instanceof Command);

        $name = $command->getName();
        $class = get_class($command);
    }

    public function recordError(ConsoleErrorEvent $event): void
    {
    }

    public function recordStatus(ConsoleTerminateEvent $event): void
    {
    }

    public function recordSignal(ConsoleSignalEvent $event): void
    {
    }
}
