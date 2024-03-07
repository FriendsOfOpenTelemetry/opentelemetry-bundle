<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Doctrine\Middleware;

use Doctrine\DBAL\Driver\Exception;
use OpenTelemetry\API\Trace\SpanInterface;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\API\Trace\StatusCode;
use OpenTelemetry\API\Trace\TracerInterface;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SemConv\TraceAttributes;
use Psr\Log\LoggerInterface;

class Tracer
{
    public function __construct(
        private TracerInterface $tracer,
        private ?LoggerInterface $logger = null,
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
        $scope = Context::storage()->scope();
        if (null !== $scope) {
            $this->logger?->debug(sprintf('Using scope "%s"', spl_object_id($scope)));
        }
        $span = null;

        try {
            $spanBuilder = $this->tracer
                ->spanBuilder($name)
                ->setSpanKind(SpanKind::KIND_CLIENT)
                ->setParent($scope?->context())
            ;

            $span = $spanBuilder->startSpan();

            $this->logger?->debug(sprintf('Starting span "%s"', $span->getContext()->getSpanId()));

            return $callback($span);
        } catch (Exception $exception) {
            if ($span instanceof SpanInterface) {
                $span->recordException($exception, [TraceAttributes::EXCEPTION_ESCAPED => true]);
                $span->setStatus(StatusCode::STATUS_ERROR, $exception->getMessage());
            }
            throw $exception;
        } finally {
            if ($span instanceof SpanInterface) {
                $this->logger?->debug(sprintf('Ending span "%s"', $span->getContext()->getSpanId()));
                $span->end();
            }
        }
    }
}
