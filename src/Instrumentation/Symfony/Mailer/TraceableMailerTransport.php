<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Symfony\Mailer;

use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\API\Trace\StatusCode;
use OpenTelemetry\API\Trace\TracerInterface;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SemConv\TraceAttributes;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\RawMessage;

final readonly class TraceableMailerTransport implements TransportInterface
{
    public function __construct(
        private TransportInterface $transport,
        private TracerInterface $tracer,
    ) {
    }

    public function __toString()
    {
        return (string) $this->transport;
    }

    public function send(RawMessage $message, Envelope $envelope = null): ?SentMessage
    {
        $scope = Context::storage()->scope();
        if (null === $scope) {
            return $this->transport->send($message, $envelope);
        }

        $span = null;

        try {
            $spanBuilder = $this->tracer
                ->spanBuilder('mailer.transport.send')
                ->setSpanKind(SpanKind::KIND_INTERNAL)
            ;

            $span = $spanBuilder->setParent(Context::getCurrent())->startSpan();

            return $this->transport->send($message, $envelope);
        } catch (TransportException $exception) {
            if (null !== $span) {
                $span->recordException($exception, [TraceAttributes::EXCEPTION_ESCAPED => true]);
                $span->setStatus(StatusCode::STATUS_ERROR, $exception->getMessage());
            }
            throw $exception;
        } finally {
            $span?->end();
        }
    }
}
