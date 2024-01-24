<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Symfony\Messenger;

use OpenTelemetry\API\Trace\SpanInterface;
use OpenTelemetry\API\Trace\TracerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\TransportInterface;

final readonly class TraceableMessengerTransport implements TransportInterface
{
    private TransportTracer $tracer;

    public function __construct(
        private TransportInterface $transport,
        TracerInterface $tracer,
    ) {
        $this->tracer = new TransportTracer($tracer);
    }

    public function get(): iterable
    {
        return $this->tracer->traceFunction('messenger.transport.get', function (?SpanInterface $span) {
            return $this->transport->get();
        });
    }

    public function ack(Envelope $envelope): void
    {
        $this->tracer->traceFunction('messenger.transport.ack', function (?SpanInterface $span) use ($envelope) {
            $this->transport->ack($envelope);
        });
    }

    public function reject(Envelope $envelope): void
    {
        $this->tracer->traceFunction('messenger.transport.reject', function (?SpanInterface $span) use ($envelope) {
            $this->transport->reject($envelope);
        });
    }

    public function send(Envelope $envelope): Envelope
    {
        return $this->tracer->traceFunction('messenger.transport.send', function (?SpanInterface $span) use ($envelope) {
            return $this->transport->send($envelope);
        });
    }
}
