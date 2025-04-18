<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Symfony\Messenger;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\InstrumentationTypeEnum;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\API\Trace\StatusCode;
use OpenTelemetry\API\Trace\TracerInterface;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Trace\Span;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;
use Symfony\Component\Messenger\Event\WorkerMessageHandledEvent;
use Symfony\Component\Messenger\Event\WorkerMessageReceivedEvent;
use Symfony\Component\Messenger\Stamp\BusNameStamp;

/**
 * Be aware if you start a span before this subscriber, it could leads to orphan span issue.
 * Be sure your span is properly ended.
 */
class InstrumentationEventSubscriber implements EventSubscriberInterface
{
    private ?InstrumentationTypeEnum $instrumentationType = null;

    public function __construct(
        private readonly TracerInterface $tracer,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function setInstrumentationType(InstrumentationTypeEnum $instrumentationType): void
    {
        $this->instrumentationType = $instrumentationType;
    }

    public static function getSubscribedEvents()
    {
        return [
            WorkerMessageReceivedEvent::class => ['startSpan'],
            WorkerMessageFailedEvent::class => ['endSpanOnError'],
            WorkerMessageHandledEvent::class => ['endSpanWithSuccess'],
        ];
    }

    public function startSpan(WorkerMessageReceivedEvent $event): void
    {
        if (InstrumentationTypeEnum::Auto !== $this->instrumentationType) {
            return;
        }

        $scope = Context::storage()->scope();

        if (null !== $scope) {
            $this->logger->debug(sprintf('Using scope "%s"', spl_object_id($scope)));
        } else {
            $this->logger->debug('No active scope');
        }

        $context = Context::getCurrent();
        $span = $this->tracer
            ->spanBuilder($event->getReceiverName())
            ->setParent($context)
            ->setSpanKind(SpanKind::KIND_CONSUMER)
            ->startSpan();

        $busNameStamp = $event->getEnvelope()->last(BusNameStamp::class);

        if (null !== $busNameStamp) {
            $span->setAttribute('bus.name', $busNameStamp->getBusName());
        }

        $this->logger->debug(sprintf('Starting span "%s"', $span->getContext()->getSpanId()));

        Context::storage()
            ->attach(
                $span->storeInContext($context)
            )
        ;
    }

    public function endSpanWithSuccess(WorkerMessageHandledEvent $event): void
    {
        if (InstrumentationTypeEnum::Auto !== $this->instrumentationType) {
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

    public function endSpanOnError(WorkerMessageFailedEvent $event): void
    {
        if (InstrumentationTypeEnum::Auto !== $this->instrumentationType) {
            return;
        }

        $scope = Context::storage()->scope();

        if (null === $scope) {
            return;
        }

        $scope->detach();

        $span = Span::fromContext($scope->context());
        $span->setStatus(StatusCode::STATUS_ERROR);
        $span->setAttribute('exception.message', $event->getThrowable()->getMessage());
        $previous = $event->getThrowable()->getPrevious();

        if (null !== $previous) {
            $span->setAttribute('exception.previous.message', $previous->getMessage());
        }

        $this->logger->debug(sprintf('Ending span "%s"', $span->getContext()->getSpanId()));
        $span->end();
    }
}
