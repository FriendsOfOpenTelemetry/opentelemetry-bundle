<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Symfony\Messenger;

use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\API\Trace\StatusCode;
use OpenTelemetry\API\Trace\TracerInterface;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Trace\Span;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;

class TraceableMessengerStack implements StackInterface
{
    private ?string $currentEvent = null;

    public function __construct(
        private TracerInterface $tracer,
        private StackInterface $stack,
        private string $busName,
        private string $eventCategory,
        private ?LoggerInterface $logger = null,
    ) {
    }

    public function next(): MiddlewareInterface
    {
        $scope = Context::storage()->scope();
        if (null !== $scope) {
            $this->logger?->debug(sprintf('Using scope "%s"', spl_object_id($scope)));
        } else {
            $this->logger?->debug('No active scope');
        }

        /*        if (null !== $scope) {
                    $span = Span::fromContext($scope->context());

                    if ($span->isRecording()) {
                        $scope->detach();

                        $span->setStatus(StatusCode::STATUS_OK);
                        $this->logger?->debug(sprintf('Ending span "%s"', $span->getContext()->getSpanId()));
                        $span->end();
                    }
                }*/

        $spanBuilder = $this->tracer
            ->spanBuilder('messenger.middleware')
            ->setSpanKind(SpanKind::KIND_INTERNAL)
            ->setParent($scope?->context())
            ->setAttribute('event.category', $this->eventCategory)
            ->setAttribute('bus.name', $this->busName)
        ;

        $parent = Context::getCurrent();

        $span = $spanBuilder->setParent($parent)->startSpan();

        $this->logger?->debug(sprintf('Starting span "%s"', $span->getContext()->getSpanId()));

        if ($this->stack === $nextMiddleware = $this->stack->next()) {
            $this->currentEvent = 'Tail';
        } else {
            $this->currentEvent = sprintf('"%s"', get_debug_type($nextMiddleware));
        }
        $this->currentEvent .= sprintf(' on "%s"', $this->busName);

        $span->setAttribute('event.current', $this->currentEvent);

        $context = $span->storeInContext($parent);
        Context::storage()->attach($context);

        return $nextMiddleware;
    }

    public function stop(): void
    {
        $scope = Context::storage()->scope();
        if (null === $scope) {
            return;
        }

        $scope->detach();

        $span = Span::fromContext($scope->context());
        $span->setStatus(StatusCode::STATUS_OK);
        $this->logger?->debug(sprintf('Ending span "%s"', $span->getContext()->getSpanId()));
        $span->end();
        $this->currentEvent = null;
    }

    public function __clone()
    {
        $this->stack = clone $this->stack;
    }
}
