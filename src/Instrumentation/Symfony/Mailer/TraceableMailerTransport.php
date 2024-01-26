<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Symfony\Mailer;

use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\API\Trace\StatusCode;
use OpenTelemetry\API\Trace\TracerInterface;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SemConv\TraceAttributes;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\RawMessage;

final readonly class TraceableMailerTransport implements TransportInterface
{
    public function __construct(
        private TransportInterface $transport,
        private TracerInterface $tracer,
        private LoggerInterface $logger,
    ) {
    }

    public function __toString()
    {
        return (string) $this->transport;
    }

    public function send(RawMessage $message, ?Envelope $envelope = null): ?SentMessage
    {
        $scope = Context::storage()->scope();
        if (null === $scope) {
            $this->logger->debug('No scope is available to register new spans.');

            return $this->transport->send($message, $envelope);
        }

        $span = null;

        try {
            $spanBuilder = $this->tracer
                ->spanBuilder('mailer.transport.send')
                ->setSpanKind(SpanKind::KIND_CLIENT)
            ;

            $span = $spanBuilder->setParent(Context::getCurrent())->startSpan();

            if ($message instanceof Email) {
                $headers = $message->getHeaders()->addTextHeader('X-Trace', $span->getContext()->getTraceId());
                $message = $message->setHeaders($headers);
            }

            return $this->transport->send(
                $message,
                $envelope,
            );
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
