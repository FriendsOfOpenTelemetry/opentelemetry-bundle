<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Symfony\Messenger;

use OpenTelemetry\API\Trace\SpanInterface;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\API\Trace\StatusCode;
use OpenTelemetry\API\Trace\TracerInterface;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ScopeInterface;
use OpenTelemetry\SemConv\TraceAttributes;
use Symfony\Component\Messenger\Exception\TransportException;

class TransportTracer
{
    private ?ScopeInterface $scope = null;

    public function __construct(
        private TracerInterface $tracer
    ) {
    }

    /**
     * @phpstan-template T
     *
     * @phpstan-param callable(SpanInterface|null): T $callback
     *
     * @phpstan-return T
     */
    public function traceFunction(string $name, callable $callback)
    {
        $scope = Context::storage()->scope();
        $span = null;

        try {
            $spanBuilder = $this->tracer
                ->spanBuilder($name)
                ->setSpanKind(SpanKind::KIND_INTERNAL)
            ;

            $span = $spanBuilder->setParent($scope?->context())->startSpan();
            if (null === $scope && null !== $this->scope) {
                $this->scope = $span->storeInContext(Context::getCurrent())->activate();
            }

            return $callback($span);
        } catch (TransportException $exception) {
            if (null !== $span) {
                $span->recordException($exception, [TraceAttributes::EXCEPTION_ESCAPED => true]);
                $span->setStatus(StatusCode::STATUS_ERROR, $exception->getMessage());
            }
            throw $exception;
        } finally {
            $this->scope?->detach();
            $this->scope = null;
            $span?->end();
        }
    }
}
