<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\EventSubscriber;

use OpenTelemetry\API\Metrics\CounterInterface;
use OpenTelemetry\API\Metrics\MeterInterface;
use OpenTelemetry\SDK\Metrics\MeterProviderInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Event\ConsoleErrorEvent;
use Symfony\Component\Console\Event\ConsoleSignalEvent;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class ConsoleMetricEventSubscriber implements EventSubscriberInterface
{
    private CounterInterface $totalCommandsCounter;
    private CounterInterface $commandCounter;

    public function __construct(
        private readonly MeterInterface $meter,
        private readonly MeterProviderInterface $meterProvider,
    ) {
        $this->totalCommandsCounter = $this->meter->createCounter('symfony_console_commands');
        $this->commandCounter = $this->meter->createCounter('symfony_console_command');
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
                ['flushMeterProvider', -20000],
            ],
            ConsoleEvents::SIGNAL => [
                ['recordSignal', -10000],
            ],
        ];
    }

    public function recordCommand(ConsoleCommandEvent $event): void
    {
        $this->totalCommandsCounter->add(1);

        $command = $event->getCommand();

        assert($command instanceof Command);

        $name = $command->getName();
        $class = get_class($command);

        $this->commandCounter->add(1, [
            'name' => $name,
            'class' => $class,
        ]);
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

    public function flushMeterProvider(ConsoleTerminateEvent $event): void
    {
        $this->meterProvider->forceFlush();
    }
}
