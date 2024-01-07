<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Middleware\Doctrine\Trace;

use Doctrine\DBAL\Driver\Exception;
use OpenTelemetry\API\Trace\SpanInterface;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\API\Trace\StatusCode;
use OpenTelemetry\API\Trace\TracerInterface;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SemConv\TraceAttributes;

final readonly class DoctrineTracer
{
    public function __construct(
        private TracerInterface $tracer
    ) {
    }

    /**
     * @phpstan-template T
     *
     * @phpstan-param callable(SpanInterface): T $callback
     *
     * @phpstan-return T
     */
    public function traceFunction(string $name, callable $callback)
    {
        $span = null;

        try {
            $spanBuilder = $this->tracer
                ->spanBuilder($name)
                ->setSpanKind(SpanKind::KIND_CLIENT)
            ;

            $span = $spanBuilder->setParent(Context::getCurrent())->startSpan();

            return $callback($span);
        } catch (Exception $exception) {
            if (null !== $span) {
                $span->recordException($exception, [TraceAttributes::EXCEPTION_ESCAPED => true]);
                $span->setStatus(StatusCode::STATUS_ERROR, $exception->getMessage());
            }
        } finally {
            $span?->end();
        }
    }
}
