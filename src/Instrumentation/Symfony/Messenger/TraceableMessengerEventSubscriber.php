<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Symfony\Messenger;

use OpenTelemetry\API\Trace\TracerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Event\SendMessageToTransportsEvent;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;
use Symfony\Component\Messenger\Event\WorkerMessageHandledEvent;
use Symfony\Component\Messenger\Event\WorkerMessageReceivedEvent;
use Symfony\Component\Messenger\Event\WorkerMessageRetriedEvent;
use Symfony\Component\Messenger\Event\WorkerRateLimitedEvent;
use Symfony\Component\Messenger\Event\WorkerRunningEvent;
use Symfony\Component\Messenger\Event\WorkerStartedEvent;
use Symfony\Component\Messenger\Event\WorkerStoppedEvent;

final readonly class TraceableMessengerEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        /** @phpstan-ignore-next-line */
        private TracerInterface $tracer,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SendMessageToTransportsEvent::class => 'onMessageToTransport',
            WorkerMessageFailedEvent::class => 'onFailedWorkerMessage',
            WorkerMessageHandledEvent::class => 'onHandledWorkerMessage',
            WorkerMessageReceivedEvent::class => 'onReceivedWorkerMessage',
            WorkerMessageRetriedEvent::class => 'onRetriedWorkerMessage',
            WorkerRateLimitedEvent::class => 'onRatedLimitedWorker',
            WorkerRunningEvent::class => 'onRunningWorker',
            WorkerStartedEvent::class => 'onStartedWorker',
            WorkerStoppedEvent::class => 'onWorkerStopped',
        ];
    }

    public function onMessageToTransport(SendMessageToTransportsEvent $event): void
    {
    }

    public function onFailedWorkerMessage(WorkerMessageFailedEvent $event): void
    {
    }

    public function onHandledWorkerMessage(WorkerMessageHandledEvent $event): void
    {
    }

    public function onReceivedWorkerMessage(WorkerMessageReceivedEvent $event): void
    {
    }

    public function onRetriedWorkerMessage(WorkerMessageRetriedEvent $event): void
    {
    }

    public function onRatedLimitedWorker(WorkerRateLimitedEvent $event): void
    {
    }

    public function onRunningWorker(WorkerRunningEvent $event): void
    {
    }

    public function onStartedWorker(WorkerStartedEvent $event): void
    {
    }

    public function onWorkerStopped(WorkerStoppedEvent $event): void
    {
    }
}
