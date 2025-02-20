<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Symfony\Cache;

use OpenTelemetry\API\Trace\SpanInterface;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\API\Trace\StatusCode;
use OpenTelemetry\API\Trace\TracerInterface;
use OpenTelemetry\Context\Context;
use Psr\Cache\CacheException;
use Psr\Log\LoggerInterface;

class Tracer
{
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
        } else {
            $this->logger?->debug('No active scope');
        }
        $span = null;

        try {
            $spanBuilder = $this->tracer
                ->spanBuilder($name)
                ->setSpanKind(SpanKind::KIND_CLIENT)
            ;

            $span = $spanBuilder->setParent($scope?->context())->startSpan();

            $this->logger?->debug(sprintf('Starting span "%s"', $span->getContext()->getSpanId()));

            return $callback($span);
        } catch (CacheException $exception) {
            if ($span instanceof SpanInterface) {
                $span->recordException($exception);
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
