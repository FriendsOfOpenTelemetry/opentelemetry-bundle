<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Symfony\Cache;

use OpenTelemetry\API\Trace\SpanInterface;
use OpenTelemetry\API\Trace\TracerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\PruneableInterface;
use Symfony\Component\Cache\ResettableInterface;
use Symfony\Contracts\Cache\CacheInterface;

class TraceableCacheAdapter implements AdapterInterface, CacheInterface, PruneableInterface, ResettableInterface
{
    use TraceableCacheAdapterTrait;

    public function __construct(
        TracerInterface $tracer,
        AdapterInterface $adapter,
        ?LoggerInterface $logger = null,
    ) {
        $this->tracer = new Tracer($tracer, $logger);
        $this->adapter = $adapter;
        $this->logger = $logger;
    }

    public function get(string $key, callable $callback, ?float $beta = null, ?array &$metadata = null): mixed
    {
        return $this->tracer->traceFunction(
            'cache.get',
            function (?SpanInterface $span) use ($key, $callback, $beta, $metadata): mixed {
                if (!$this->adapter instanceof CacheInterface) {
                    throw new \BadMethodCallException(sprintf('The %s::get() method is not supported because the decorated adapter does not implement the "%s" interface.', self::class, CacheInterface::class));
                }
                $span?->setAttribute('cache.get', $key);

                return $this->adapter->get($key, $callback, $beta, $metadata);
            }
        );
    }
}
