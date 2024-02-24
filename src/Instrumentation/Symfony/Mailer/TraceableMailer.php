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
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\RawMessage;

class TraceableMailer implements MailerInterface
{
    private ?ScopeInterface $scope = null;

    public function __construct(
        private TracerInterface $tracer,
        private MailerInterface $mailer,
        /** @phpstan-ignore-next-line */
        private LoggerInterface $logger,
    ) {
    }

    public function send(RawMessage $message, ?Envelope $envelope = null): void
    {
        $scope = Context::storage()->scope();
        $span = null;

        try {
            $spanBuilder = $this->tracer
                ->spanBuilder('mailer.send')
                ->setSpanKind(SpanKind::KIND_INTERNAL)
            ;

            // TODO: Parse RawMessage implementations to set span attributes

            $span = $spanBuilder->setParent($scope?->context())->startSpan();
            if (null === $scope && null === $this->scope) {
                $this->scope = $span->storeInContext(Context::getCurrent())->activate();
            }

            $this->mailer->send($message, $envelope);
        } catch (TransportException $exception) {
            if ($span instanceof SpanInterface) {
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
