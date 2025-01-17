<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Functional\Instrumentation\Cache;

use App\Kernel;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Functional\TracingTestCaseTrait;
use OpenTelemetry\SDK\Trace\StatusData;
use Psr\Cache\CacheItemInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\PruneableInterface;
use Symfony\Component\Cache\ResettableInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Zalas\PHPUnit\Globals\Attribute\Env;

#[Env('KERNEL_CLASS', Kernel::class)]
class CacheTracingTest extends KernelTestCase
{
    use TracingTestCaseTrait;

    private AdapterInterface&CacheInterface&PruneableInterface&ResettableInterface $cache;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->cache = self::getContainer()->get('cache.app');
    }

    public function testGet(): void
    {
        $this->cache->get('foo', fn (CacheItemInterface $item): string => 'bar');

        self::assertSpansCount(1);

        $mainSpan = self::getSpans()[0];
        self::assertSpanName($mainSpan, 'cache.get');
        self::assertSpanStatus($mainSpan, StatusData::unset());
        self::assertSpanAttributes($mainSpan, [
            'cache.get' => 'foo',
        ]);
        self::assertSpanEventsCount($mainSpan, 0);
    }

    public function testGetItem(): void
    {
        $this->cache->getItem('foo');

        self::assertSpansCount(1);

        $mainSpan = self::getSpans()[0];
        self::assertSpanName($mainSpan, 'cache.get_item');
        self::assertSpanStatus($mainSpan, StatusData::unset());
        self::assertSpanAttributes($mainSpan, [
            'cache.item.get' => 'foo',
        ]);
        self::assertSpanEventsCount($mainSpan, 0);
    }

    public function testGetItems(): void
    {
        $this->cache->getItems(['foo', 'bar']);

        self::assertSpansCount(1);

        $mainSpan = self::getSpans()[0];
        self::assertSpanName($mainSpan, 'cache.get_items');
        self::assertSpanStatus($mainSpan, StatusData::unset());
        self::assertSpanAttributes($mainSpan, [
            'cache.items.get' => ['foo', 'bar'],
        ]);
        self::assertSpanEventsCount($mainSpan, 0);
    }

    public function testClear(): void
    {
        $this->cache->clear('foo-');

        self::assertSpansCount(1);

        $mainSpan = self::getSpans()[0];
        self::assertSpanName($mainSpan, 'cache.clear');
        self::assertSpanStatus($mainSpan, StatusData::unset());
        self::assertSpanAttributes($mainSpan, [
            'cache.prefix' => 'foo-',
        ]);
        self::assertSpanEventsCount($mainSpan, 0);
    }

    public function testDelete(): void
    {
        $this->cache->delete('foo');

        self::assertSpansCount(1);

        $mainSpan = self::getSpans()[0];
        self::assertSpanName($mainSpan, 'cache.delete');
        self::assertSpanStatus($mainSpan, StatusData::unset());
        self::assertSpanAttributes($mainSpan, [
            'cache.delete' => 'foo',
        ]);
        self::assertSpanEventsCount($mainSpan, 0);
    }

    public function testHasItem(): void
    {
        $this->cache->hasItem('foo');

        self::assertSpansCount(1);

        $mainSpan = self::getSpans()[0];
        self::assertSpanName($mainSpan, 'cache.has_item');
        self::assertSpanStatus($mainSpan, StatusData::unset());
        self::assertSpanAttributes($mainSpan, [
            'cache.item.has' => 'foo',
        ]);
        self::assertSpanEventsCount($mainSpan, 0);
    }

    public function testDeleteItem(): void
    {
        $this->cache->deleteItem('foo');

        self::assertSpansCount(1);

        $mainSpan = self::getSpans()[0];
        self::assertSpanName($mainSpan, 'cache.delete_item');
        self::assertSpanStatus($mainSpan, StatusData::unset());
        self::assertSpanAttributes($mainSpan, [
            'cache.item.delete' => 'foo',
        ]);
        self::assertSpanEventsCount($mainSpan, 0);
    }

    public function testDeleteItems(): void
    {
        $this->cache->deleteItems(['foo', 'bar']);

        self::assertSpansCount(1);

        $mainSpan = self::getSpans()[0];
        self::assertSpanName($mainSpan, 'cache.delete_items');
        self::assertSpanStatus($mainSpan, StatusData::unset());
        self::assertSpanAttributes($mainSpan, [
            'cache.items.delete' => ['foo', 'bar'],
        ]);
        self::assertSpanEventsCount($mainSpan, 0);
    }

    public function testSave(): void
    {
        $item = $this->cache->getItem('foo');
        $this->cache->save($item);

        self::assertSpansCount(2);

        $mainSpan = self::getSpans()[1];
        self::assertSpanName($mainSpan, 'cache.save');
        self::assertSpanStatus($mainSpan, StatusData::unset());
        self::assertSpanAttributes($mainSpan, [
            'cache.item.save' => 'foo',
        ]);
        self::assertSpanEventsCount($mainSpan, 0);
    }

    public function testSaveDeferred(): void
    {
        $item = $this->cache->getItem('foo');
        $this->cache->saveDeferred($item);

        self::assertSpansCount(2);

        $mainSpan = self::getSpans()[1];
        self::assertSpanName($mainSpan, 'cache.save_deferred');
        self::assertSpanStatus($mainSpan, StatusData::unset());
        self::assertSpanAttributes($mainSpan, [
            'cache.item.save_deferred' => 'foo',
        ]);
        self::assertSpanEventsCount($mainSpan, 0);
    }

    public function testCommit(): void
    {
        $item = $this->cache->getItem('foo');
        $this->cache->saveDeferred($item);
        $this->cache->commit();

        self::assertSpansCount(3);

        $mainSpan = self::getSpans()[2];
        self::assertSpanName($mainSpan, 'cache.commit');
        self::assertSpanStatus($mainSpan, StatusData::unset());
        self::assertSpanAttributes($mainSpan, []);
        self::assertSpanEventsCount($mainSpan, 0);
    }

    public function testPrune(): void
    {
        $this->cache->prune();

        self::assertSpansCount(1);

        $mainSpan = self::getSpans()[0];
        self::assertSpanName($mainSpan, 'cache.prune');
        self::assertSpanStatus($mainSpan, StatusData::unset());
        self::assertSpanAttributes($mainSpan, []);
        self::assertSpanEventsCount($mainSpan, 0);
    }

    public function testReset(): void
    {
        $this->cache->reset();

        self::assertSpansCount(1);

        $mainSpan = self::getSpans()[0];
        self::assertSpanName($mainSpan, 'cache.reset');
        self::assertSpanStatus($mainSpan, StatusData::unset());
        self::assertSpanAttributes($mainSpan, []);
        self::assertSpanEventsCount($mainSpan, 0);
    }
}
