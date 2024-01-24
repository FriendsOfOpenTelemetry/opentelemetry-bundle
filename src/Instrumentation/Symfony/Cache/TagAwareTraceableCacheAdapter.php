<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Symfony\Cache;

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
        TagAwareAdapterInterface $adapter,
    ) {
        $this->tracer = new Tracer($tracer);
        $this->adapter = $adapter;
    }

    public function get(string $key, callable $callback, float $beta = null, array &$metadata = null): mixed
    {
        return $this->tracer->traceFunction(
            'cache.get',
            function (?SpanInterface $span) use ($key, $callback, $beta, $metadata): bool {
                if (!$this->adapter instanceof CacheInterface) {
                    throw new \BadMethodCallException(sprintf('The %s::get() method is not supported because the decorated adapter does not implement the "%s" interface.', self::class, CacheInterface::class));
                }
                $span?->setAttribute('cache.get', $key);

                return $this->adapter->get($key, $callback, $beta, $metadata);
            });
    }

    public function invalidateTags(array $tags): bool
    {
        return $this->tracer->traceFunction('cache.invalidate_tags', function (?SpanInterface $span) use ($tags): bool {
            if (!$this->adapter instanceof TagAwareAdapterInterface) {
                throw new \BadMethodCallException(sprintf('The %s::invalidateTags() method is not supported because the decorated adapter does not implement the "%s" interface.', self::class, TagAwareAdapterInterface::class));
            }
            $span?->setAttribute('cache.invalidate_tags', $tags);

            return $this->adapter->invalidateTags($tags);
        });
    }
}
