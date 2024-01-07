<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Symfony\Messenger;

use OpenTelemetry\API\Trace\TracerInterface;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Messenger\Transport\TransportFactory;
use Symfony\Component\Messenger\Transport\TransportFactoryInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;
use Zenstruck\Dsn;

/**
 * @implements TransportFactoryInterface<TraceableMessengerTransport>
 */
class TraceableMessengerTransportFactory implements TransportFactoryInterface
{
    public function __construct(
        private TransportFactory $transportFactory,
        private TracerInterface $tracer,
    ) {
    }

    public function createTransport(#[\SensitiveParameter] string $dsn, array $options, SerializerInterface $serializer): TransportInterface
    {
        $transport = $this->transportFactory->createTransport(Dsn::parse($dsn)->inner(), $options, $serializer);

        return new TraceableMessengerTransport($transport, $this->tracer);
    }

    public function supports(#[\SensitiveParameter] string $dsn, array $options): bool
    {
        return Dsn::parse($dsn)->scheme()->equals('trace');
    }
}
