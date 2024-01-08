<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Symfony\Adapter\Cache;

use OpenTelemetry\API\Trace\SpanInterface;
use OpenTelemetry\API\Trace\TracerInterface;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;
use Symfony\Component\Cache\PruneableInterface;
use Symfony\Component\Cache\ResettableInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class TagAwareTraceableCacheAdapter implements TagAwareAdapterInterface, TagAwareCacheInterface, PruneableInterface, ResettableInterface
{
    use TraceableCacheAdapterTrait;

    public function __construct(
        TracerInterface $tracer,
        private readonly TagAwareAdapterInterface $adapter,
    ) {
        $this->tracer = new CacheTracer($tracer);
    }

    public function get(string $key, callable $callback, float $beta = null, array &$metadata = null): mixed
    {
        return $this->tracer->traceFunction(
            'cache.get',
            function (SpanInterface $span) use ($key, $callback, $beta, $metadata): bool {
                if (!$this->adapter instanceof CacheInterface) {
                    throw new \BadMethodCallException(sprintf('The %s::get() method is not supported because the decorated adapter does not implement the "%s" interface.', self::class, CacheInterface::class));
                }

                return $this->adapter->get($key, $callback, $beta, $metadata);
            });
    }

    public function invalidateTags(array $tags): bool
    {
        return $this->tracer->traceFunction('cache.invalidate_tags', function (SpanInterface $span) use ($tags): bool {
            return $this->adapter->invalidateTags($tags);
        });
    }
}
