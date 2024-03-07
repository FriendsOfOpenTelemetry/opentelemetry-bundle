<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Symfony\Messenger;

use OpenTelemetry\API\Trace\SpanInterface;
use OpenTelemetry\API\Trace\StatusCode;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;

class TraceableMessengerStack implements StackInterface
{
    private ?string $currentEvent = null;

    public function __construct(
        private SpanInterface $span,
        private StackInterface $stack,
        private string $busName,
        private ?LoggerInterface $logger = null,
    ) {
    }

    public function next(): MiddlewareInterface
    {
        if ($this->stack === $nextMiddleware = $this->stack->next()) {
            $this->currentEvent = 'Tail';
        } else {
            $this->currentEvent = sprintf('"%s"', get_debug_type($nextMiddleware));
        }
        $this->currentEvent .= sprintf(' on "%s"', $this->busName);

        $this->span->setAttribute('event.current', $this->currentEvent);

        return $nextMiddleware;
    }

    public function stop(): void
    {
        $this->span->setStatus(StatusCode::STATUS_OK);
        $this->logger?->debug(sprintf('Ending span "%s"', $this->span->getContext()->getSpanId()));
        $this->span->end();
        $this->currentEvent = null;
    }

    public function __clone()
    {
        $this->stack = clone $this->stack;
    }
}
