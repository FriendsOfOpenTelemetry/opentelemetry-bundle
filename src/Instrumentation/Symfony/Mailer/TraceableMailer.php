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
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\RawMessage;

final readonly class TraceableMailer implements MailerInterface
{
    public function __construct(
        private TracerInterface $tracer,
        private MailerInterface $mailer,
        private LoggerInterface $logger,
    ) {
    }

    public function send(RawMessage $message, Envelope $envelope = null): void
    {
        $scope = Context::storage()->scope();
        if (null === $scope) {
            $this->logger->debug('No scope is available to register new spans.');
            $this->mailer->send($message, $envelope);

            return;
        }

        $span = null;

        try {
            $spanBuilder = $this->tracer
                ->spanBuilder('mailer.send')
                ->setSpanKind(SpanKind::KIND_INTERNAL)
            ;

            // TODO: Parse RawMessage implementations to set span attributes

            $span = $spanBuilder->setParent(Context::getCurrent())->startSpan();

            $this->mailer->send($message, $envelope);
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
