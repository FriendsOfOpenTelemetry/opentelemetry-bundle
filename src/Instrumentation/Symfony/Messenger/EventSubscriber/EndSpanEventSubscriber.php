<?php

declare(strict_types=1);

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Symfony\Messenger\EventSubscriber;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\InstrumentationTypeEnum;
use OpenTelemetry\API\Trace\StatusCode;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Trace\Span;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;
use Symfony\Component\Messenger\Event\WorkerRunningEvent;
use Symfony\Component\Messenger\Event\WorkerStoppedEvent;

class EndSpanEventSubscriber implements EventSubscriberInterface
{
    private ?InstrumentationTypeEnum $instrumentationType = null;

    public function __construct(
        private readonly LoggerInterface $logger
    ) {

    }

    public function setInstrumentationType(InstrumentationTypeEnum $instrumentationType): void
    {
        $this->instrumentationType = $instrumentationType;
    }

    public static function getSubscribedEvents()
    {
        return [
            WorkerRunningEvent::class => ['endSpan'],
            WorkerStoppedEvent::class => ['endSpan'],
            WorkerMessageFailedEvent::class => ['hydrateSpanWithError'],
        ];
    }

    public function endSpan(WorkerRunningEvent|WorkerStoppedEvent $event): void
    {
        if ($this->instrumentationType !== InstrumentationTypeEnum::Auto) {
            return;
        }

        $scope = Context::storage()->scope();

        if (null === $scope) {
            return;
        }

        $scope->detach();

        $span = Span::fromContext($scope->context());
        $span->setStatus(StatusCode::STATUS_OK);
        $this->logger->debug(sprintf('Ending span "%s"', $span->getContext()->getSpanId()));
        $span->end();
    }

    public function hydrateSpanWithError(WorkerMessageFailedEvent $event): void
    {
        if ($this->instrumentationType !== InstrumentationTypeEnum::Auto) {
            return;
        }

        $scope = Context::storage()->scope();

        if (null === $scope) {
            return;
        }

        $span = Span::fromContext($scope->context());
        $span->setAttribute('exception.message', $event->getThrowable()->getMessage());
        $previous = $event->getThrowable()->getPrevious();
        if ($previous !== null) {
            $span->setAttribute('exception.previous.message', $previous->getMessage());
        }
    }
}
