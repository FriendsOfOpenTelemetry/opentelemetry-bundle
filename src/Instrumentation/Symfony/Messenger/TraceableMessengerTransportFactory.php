<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Symfony\Messenger;

use OpenTelemetry\API\Trace\TracerInterface;
use Psr\Log\LoggerInterface;
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
        private ?LoggerInterface $logger = null,
    ) {
    }

    /**
     * @param array<mixed> $options
     */
    public function createTransport(#[\SensitiveParameter] string $dsn, array $options, SerializerInterface $serializer): TransportInterface
    {
        $transport = $this->transportFactory->createTransport(Dsn::parse($dsn)->inner(), $options, $serializer);

        return new TraceableMessengerTransport($transport, $this->tracer, $this->logger);
    }

    /**
     * @param array<mixed> $options
     */
    public function supports(#[\SensitiveParameter] string $dsn, array $options): bool
    {
        $dsn = Dsn::parse($dsn);
        if (!$dsn instanceof Dsn\Decorated) {
            return false;
        }

        return $dsn->scheme()->equals('trace');
    }
}
