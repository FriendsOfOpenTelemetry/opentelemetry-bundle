<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Symfony\Mailer;

use OpenTelemetry\API\Trace\SpanInterface;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\API\Trace\StatusCode;
use OpenTelemetry\API\Trace\TracerInterface;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ScopeInterface;
use OpenTelemetry\SemConv\TraceAttributes;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\RawMessage;

class TraceableMailerTransport implements TransportInterface
{
    private ?ScopeInterface $scope = null;

    public function __construct(
        private TransportInterface $transport,
        private TracerInterface $tracer,
        private ?LoggerInterface $logger = null,
    ) {
    }

    public function __toString()
    {
        return (string) $this->transport;
    }

    public function send(RawMessage $message, ?Envelope $envelope = null): ?SentMessage
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
                ->spanBuilder('mailer.transport.send')
                ->setSpanKind(SpanKind::KIND_CLIENT)
            ;

            $span = $spanBuilder->setParent($scope?->context())->startSpan();

            $this->logger?->debug(sprintf('Starting span "%s"', $span->getContext()->getSpanId()));

            if (null === $scope && null === $this->scope) {
                $this->scope = $span->storeInContext(Context::getCurrent())->activate();
                $this->logger?->debug(sprintf('No active scope, activating new scope "%s"', spl_object_id($this->scope)));
            }

            if ($message instanceof Email) {
                $headers = $message->getHeaders()->addTextHeader('X-Trace', $span->getContext()->getTraceId());
                $message = $message->setHeaders($headers);
            }

            return $this->transport->send(
                $message,
                $envelope,
            );
        } catch (TransportException $exception) {
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
