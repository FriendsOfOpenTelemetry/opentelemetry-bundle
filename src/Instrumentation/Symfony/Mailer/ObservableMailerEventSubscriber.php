<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Symfony\Mailer;

use OpenTelemetry\API\Metrics\MeterInterface;
use OpenTelemetry\SDK\Metrics\MeterProviderInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\Event\FailedMessageEvent;
use Symfony\Component\Mailer\Event\MessageEvent;
use Symfony\Component\Mailer\Event\SentMessageEvent;

final readonly class ObservableMailerEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private MeterInterface $meter,
        private MeterProviderInterface $meterProvider,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            MessageEvent::class => 'onMessage',
            SentMessageEvent::class => 'onSentMessage',
            FailedMessageEvent::class => 'onFailedMessage',
        ];
    }

    public function onMessage(MessageEvent $event): void
    {
    }

    public function onSentMessage(SentMessageEvent $event): void
    {
    }

    public function onFailedMessage(FailedMessageEvent $event): void
    {
    }
}
