<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Symfony\Cache;

use OpenTelemetry\API\Trace\SpanInterface;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\API\Trace\StatusCode;
use OpenTelemetry\API\Trace\TracerInterface;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ScopeInterface;
use OpenTelemetry\SemConv\TraceAttributes;
use Psr\Cache\CacheException;
use Psr\Log\LoggerInterface;

class Tracer
{
    private ?ScopeInterface $scope = null;

    public function __construct(
        private readonly TracerInterface $tracer,
        private readonly ?LoggerInterface $logger = null,
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
        if (null !== $scope) {
            $this->logger?->debug(sprintf('Using scope "%s"', spl_object_id($scope)));
        }
        $span = null;

        try {
            $spanBuilder = $this->tracer
                ->spanBuilder($name)
                ->setSpanKind(SpanKind::KIND_CLIENT)
            ;

            $span = $spanBuilder->setParent($scope?->context())->startSpan();

            $this->logger?->debug(sprintf('Starting span "%s"', $span->getContext()->getSpanId()));

            if (null === $scope && null === $this->scope) {
                $this->scope = $span->storeInContext(Context::getCurrent())->activate();
                $this->logger?->debug(sprintf('No active scope, activating new scope "%s"', spl_object_id($this->scope)));
            }

            return $callback($span);
        } catch (CacheException $exception) {
            if ($span instanceof SpanInterface) {
                $span->recordException($exception, [TraceAttributes::EXCEPTION_ESCAPED => true]);
                $span->setStatus(StatusCode::STATUS_ERROR, $exception->getMessage());
            }
            throw $exception;
        } finally {
            if (null !== $this->scope) {
                $this->logger?->debug(sprintf('Detaching scope "%s"', spl_object_id($this->scope)));
                $this->scope->detach();
                $this->scope = null;
            }
            if ($span instanceof SpanInterface) {
                $this->logger?->debug(sprintf('Ending span "%s"', $span->getContext()->getSpanId()));
                $span->end();
            }
        }
    }
}
