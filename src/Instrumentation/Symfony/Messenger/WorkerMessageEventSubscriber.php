<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Symfony\Messenger;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\InstrumentationTypeEnum;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\InstrumentationTypeInterface;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Context\Propagator\TraceStampPropagator;
use OpenTelemetry\API\Trace\Span;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\API\Trace\StatusCode;
use OpenTelemetry\API\Trace\TracerInterface;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\Propagation\MultiTextMapPropagator;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;
use Symfony\Component\Messenger\Event\WorkerMessageHandledEvent;
use Symfony\Component\Messenger\Event\WorkerMessageReceivedEvent;
use Symfony\Component\Messenger\Stamp\BusNameStamp;

class WorkerMessageEventSubscriber implements EventSubscriberInterface, InstrumentationTypeInterface
{
    private ?InstrumentationTypeEnum $instrumentationType = null;

    public function __construct(
        private readonly MultiTextMapPropagator $propagator,
        private readonly TracerInterface $tracer,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function setInstrumentationType(InstrumentationTypeEnum $type): void
    {
        $this->instrumentationType = $type;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            WorkerMessageReceivedEvent::class => [
                ['startSpan', 10000],
            ],
            WorkerMessageFailedEvent::class => [
                ['endSpanOnError', -10000],
            ],
            WorkerMessageHandledEvent::class => [
                ['endSpanWithSuccess', -10000],
            ],
        ];
    }

    public function startSpan(WorkerMessageReceivedEvent $event): void
    {
        if (InstrumentationTypeEnum::Auto !== $this->instrumentationType) {
            return;
        }

        // ensure propagation from incoming trace
        $context = $this->propagator->extract($event->getEnvelope(), new TraceStampPropagator($this->logger));

        $messageClass = get_class($event->getEnvelope()->getMessage());

        $span = $this->tracer
            ->spanBuilder(sprintf('%s %s', $event->getReceiverName(), $messageClass))
            ->setParent($context)
            ->setSpanKind(SpanKind::KIND_CONSUMER)
            ->setAttribute('messaging.operation.type', 'process')
            ->setAttribute('messaging.destination.name', $event->getReceiverName())
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
        $exception = $event->getThrowable();
        $span->recordException($exception);
        $span->setStatus(StatusCode::STATUS_ERROR, $exception->getMessage());

        $this->logger->debug(sprintf('Ending span "%s"', $span->getContext()->getSpanId()));
        $span->end();
    }
}
