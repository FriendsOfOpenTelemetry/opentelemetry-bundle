<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Adapter\Cache\Trace;

use OpenTelemetry\API\Trace\SpanInterface;
use Psr\Cache\CacheItemInterface;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;
use Symfony\Component\Cache\CacheItem;
use Symfony\Component\Cache\PruneableInterface;
use Symfony\Component\Cache\ResettableInterface;
use Symfony\Contracts\Cache\CacheInterface;

trait CacheTraceAdapterTrait
{
    private CacheTracer $tracer;

    private AdapterInterface|TagAwareAdapterInterface $adapter;

    public function getItem(mixed $key): CacheItem
    {
        return $this->tracer->traceFunction('cache.get_item', function (SpanInterface $span) use ($key): CacheItem {
            return $this->adapter->getItem($key);
        });
    }

    public function getItems(array $keys = []): iterable
    {
        return $this->tracer->traceFunction('cache.get_items', function (SpanInterface $span) use ($keys): iterable {
            return $this->adapter->getItems($keys);
        });
    }

    public function clear(string $prefix = ''): bool
    {
        return $this->tracer->traceFunction('cache.clear', function (SpanInterface $span) use ($prefix): bool {
            return $this->adapter->clear($prefix);
        });
    }

    public function delete(string $key): bool
    {
        return $this->tracer->traceFunction('cache.delete', function (SpanInterface $span) use ($key): bool {
            if (!$this->adapter instanceof CacheInterface) {
                throw new \BadMethodCallException(sprintf('The %s::delete() method is not supported because the decorated adapter does not implement the "%s" interface.', self::class, CacheInterface::class));
            }

            return $this->adapter->delete($key);
        });
    }

    public function hasItem(string $key): bool
    {
        return $this->tracer->traceFunction('cache.has_item', function (SpanInterface $span) use ($key): bool {
            return $this->adapter->hasItem($key);
        });
    }

    public function deleteItem(string $key): bool
    {
        return $this->tracer->traceFunction('cache.delete_item', function (SpanInterface $span) use ($key): bool {
            return $this->adapter->deleteItem($key);
        });
    }

    public function deleteItems(array $keys): bool
    {
        return $this->tracer->traceFunction('cache.delete_items', function (SpanInterface $span) use ($keys): bool {
            return $this->adapter->deleteItems($keys);
        });
    }

    public function save(CacheItemInterface $item): bool
    {
        return $this->tracer->traceFunction('cache.save', function (SpanInterface $span) use ($item): bool {
            return $this->adapter->save($item);
        });
    }

    public function saveDeferred(CacheItemInterface $item): bool
    {
        return $this->tracer->traceFunction('cache.save_deferred', function (SpanInterface $span) use ($item): bool {
            return $this->adapter->saveDeferred($item);
        });
    }

    public function commit(): bool
    {
        return $this->tracer->traceFunction('cache.commit', function (SpanInterface $span): bool {
            return $this->adapter->commit();
        });
    }

    public function prune(): bool
    {
        return $this->tracer->traceFunction('cache.prune', function (SpanInterface $span): bool {
            if (!$this->adapter instanceof PruneableInterface) {
                return false;
            }

            return $this->adapter->prune();
        });
    }

    public function reset(): void
    {
        $this->tracer->traceFunction('cache.reset', function (SpanInterface $span): void {
            if ($this->adapter instanceof ResettableInterface) {
                $this->adapter->reset();
            }
        });
    }
}
