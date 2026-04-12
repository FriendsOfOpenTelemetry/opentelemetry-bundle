<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Symfony\Messenger;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Attribute\Traceable;
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
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;
use Symfony\Component\Messenger\Event\WorkerMessageHandledEvent;
use Symfony\Component\Messenger\Event\WorkerMessageReceivedEvent;
use Symfony\Component\Messenger\Exception\WrappedExceptionsInterface;
use Symfony\Component\Messenger\Stamp\BusNameStamp;
use Symfony\Component\Messenger\Stamp\RedeliveryStamp;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class WorkerMessageEventSubscriber implements EventSubscriberInterface, ServiceSubscriberInterface, InstrumentationTypeInterface
{
    private ?InstrumentationTypeEnum $instrumentationType = null;

    public function __construct(
        private readonly MultiTextMapPropagator $propagator,
        private readonly TracerInterface $tracer,
        /** @var ServiceLocator<TracerInterface> */
        private readonly ServiceLocator $tracerLocator,
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

    public static function getSubscribedServices(): array
    {
        return [];
    }

    public function startSpan(WorkerMessageReceivedEvent $event): void
    {
        $message = $event->getEnvelope()->getMessage();

        if (false === $this->isAutoTraceable() && false === $this->isAttributeTraceable($message)) {
            return;
        }

        // Clean up any lingering scope from a previous message that was not
        // properly ended (e.g. worker killed, unhandled error in another subscriber).
        $previousScope = Context::storage()->scope();
        if (null !== $previousScope) {
            $previousScope->detach();
            $orphanedSpan = Span::fromContext($previousScope->context());
            $orphanedSpan->setStatus(StatusCode::STATUS_ERROR, 'Span was not properly ended');
            $orphanedSpan->end();
            $this->logger->warning(sprintf('Cleaned up orphaned span "%s"', $orphanedSpan->getContext()->getSpanId()));
        }

        // ensure propagation from incoming trace
        $context = $this->propagator->extract($event->getEnvelope(), new TraceStampPropagator($this->logger));

        $messageClass = get_class($message);

        $span = $this->getTracer($message)
            ->spanBuilder(sprintf('%s %s', $event->getReceiverName(), $messageClass))
            ->setParent($context)
            ->setSpanKind(SpanKind::KIND_CONSUMER)
            ->setAttribute('messaging.operation.type', 'process')
            ->setAttribute('messaging.destination.name', $event->getReceiverName())
            ->startSpan();

        $busNameStamp = $event->getEnvelope()->last(BusNameStamp::class);

        if (null !== $busNameStamp) {
            $span->setAttribute('symfony.messenger.bus.name', $busNameStamp->getBusName());
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
        $scope = Context::storage()->scope();

        if (null === $scope) {
            return;
        }

        $scope->detach();

        $span = Span::fromContext($scope->context());

        $span->setAttribute('symfony.messenger.will_retry', $event->willRetry());

        $redeliveryStamp = $event->getEnvelope()->last(RedeliveryStamp::class);
        if (null !== $redeliveryStamp) {
            $span->setAttribute('symfony.messenger.retry_count', $redeliveryStamp->getRetryCount());
        }

        $exception = $event->getThrowable();

        if ($exception instanceof WrappedExceptionsInterface) {
            foreach ($exception->getWrappedExceptions() as $nestedException) {
                $span->recordException($nestedException);
            }
        } else {
            $span->recordException($exception);
        }

        $span->setStatus(StatusCode::STATUS_ERROR, $exception->getMessage());

        $this->logger->debug(sprintf('Ending span "%s"', $span->getContext()->getSpanId()));
        $span->end();
    }

    private function parseAttribute(object $message): ?Traceable
    {
        $reflection = new \ReflectionClass($message);
        $attribute = $reflection->getAttributes(Traceable::class)[0] ?? null;

        return $attribute?->newInstance();
    }

    private function getTracer(object $message): TracerInterface
    {
        $traceable = $this->parseAttribute($message);

        if (null !== $traceable?->tracer) {
            return $this->tracerLocator->get($traceable->tracer);
        }

        return $this->tracer;
    }

    private function isAutoTraceable(): bool
    {
        return InstrumentationTypeEnum::Auto === $this->instrumentationType;
    }

    private function isAttributeTraceable(object $message): bool
    {
        return InstrumentationTypeEnum::Attribute === $this->instrumentationType
            && $this->parseAttribute($message) instanceof Traceable;
    }
}
