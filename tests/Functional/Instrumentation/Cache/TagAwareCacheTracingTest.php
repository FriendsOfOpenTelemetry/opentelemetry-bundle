<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Functional\Instrumentation\Cache;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Functional\Application\Kernel;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Functional\TracingTestCaseTrait;
use OpenTelemetry\SDK\Trace\StatusData;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;
use Symfony\Component\Cache\PruneableInterface;
use Symfony\Component\Cache\ResettableInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Zalas\PHPUnit\Globals\Attribute\Env;

#[Env('KERNEL_CLASS', Kernel::class)]
class TagAwareCacheTracingTest extends KernelTestCase
{
    use TracingTestCaseTrait;

    private TagAwareAdapterInterface&CacheInterface&PruneableInterface&ResettableInterface $cache;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->cache = self::getContainer()->get('cache.app.taggable');
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        self::getSpanExporter()->forceFlush();
        self::getSpanExporter()->shutdown();
    }

    public function testGet(): void
    {
        $this->cache->get('foo_tag', function (ItemInterface $item): string {
            $item->tag('tag');

            return 'bar';
        });

        self::assertSpansCount(8);

        $mainSpan = self::getSpans()[7];
        self::assertSpanName($mainSpan, 'cache.get');
        self::assertSpanStatus($mainSpan, StatusData::unset());
        self::assertSpanAttributes($mainSpan, [
            'cache.get' => 'foo_tag',
        ]);
        self::assertSpanEventsCount($mainSpan, 0);
    }

    public function testGetItem(): void
    {
        $this->cache->getItem('foo_tag');

        self::assertSpansCount(3);

        $mainSpan = self::getSpans()[2];
        self::assertSpanName($mainSpan, 'cache.get_item');
        self::assertSpanStatus($mainSpan, StatusData::unset());
        self::assertSpanAttributes($mainSpan, [
            'cache.item.get' => 'foo_tag',
        ]);
        self::assertSpanEventsCount($mainSpan, 0);
    }

    public function testGetItems(): void
    {
        $this->cache->getItems(['foo_tag', 'bar_tag']);

        self::assertSpansCount(3);

        $mainSpan = self::getSpans()[2];
        self::assertSpanName($mainSpan, 'cache.get_items');
        self::assertSpanStatus($mainSpan, StatusData::unset());
        self::assertSpanAttributes($mainSpan, [
            'cache.items.get' => ['foo_tag', 'bar_tag'],
        ]);
        self::assertSpanEventsCount($mainSpan, 0);
    }

    public function testClear(): void
    {
        $this->cache->clear('foo_tag');

        self::assertSpansCount(2);

        $mainSpan = self::getSpans()[1];
        self::assertSpanName($mainSpan, 'cache.clear');
        self::assertSpanStatus($mainSpan, StatusData::unset());
        self::assertSpanAttributes($mainSpan, [
            'cache.prefix' => 'foo_tag',
        ]);
        self::assertSpanEventsCount($mainSpan, 0);
    }

    public function testDelete(): void
    {
        $this->cache->delete('foo_tag');

        self::assertSpansCount(2);

        $mainSpan = self::getSpans()[1];
        self::assertSpanName($mainSpan, 'cache.delete');
        self::assertSpanStatus($mainSpan, StatusData::unset());
        self::assertSpanAttributes($mainSpan, [
            'cache.delete' => 'foo_tag',
        ]);
        self::assertSpanEventsCount($mainSpan, 0);
    }

    public function testHasItem(): void
    {
        $this->cache->hasItem('foo_tag');

        self::assertSpansCount(3);

        $mainSpan = self::getSpans()[2];
        self::assertSpanName($mainSpan, 'cache.has_item');
        self::assertSpanStatus($mainSpan, StatusData::unset());
        self::assertSpanAttributes($mainSpan, [
            'cache.item.has' => 'foo_tag',
        ]);
        self::assertSpanEventsCount($mainSpan, 0);
    }

    public function testDeleteItem(): void
    {
        $this->cache->deleteItem('foo_tag');

        self::assertSpansCount(2);

        $mainSpan = self::getSpans()[1];
        self::assertSpanName($mainSpan, 'cache.delete_item');
        self::assertSpanStatus($mainSpan, StatusData::unset());
        self::assertSpanAttributes($mainSpan, [
            'cache.item.delete' => 'foo_tag',
        ]);
        self::assertSpanEventsCount($mainSpan, 0);
    }

    public function testDeleteItems(): void
    {
        $this->cache->deleteItems(['foo_tag', 'bar_tag']);

        self::assertSpansCount(2);

        $mainSpan = self::getSpans()[1];
        self::assertSpanName($mainSpan, 'cache.delete_items');
        self::assertSpanStatus($mainSpan, StatusData::unset());
        self::assertSpanAttributes($mainSpan, [
            'cache.items.delete' => ['foo_tag', 'bar_tag'],
        ]);
        self::assertSpanEventsCount($mainSpan, 0);
    }

    public function testSave(): void
    {
        $item = $this->cache->getItem('foo_tag');
        $this->cache->save($item);

        self::assertSpansCount(6);

        $mainSpan = self::getSpans()[5];
        self::assertSpanName($mainSpan, 'cache.save');
        self::assertSpanStatus($mainSpan, StatusData::unset());
        self::assertSpanAttributes($mainSpan, [
            'cache.item.save' => 'foo_tag',
        ]);
        self::assertSpanEventsCount($mainSpan, 0);
    }

    public function testSaveDeferred(): void
    {
        $item = $this->cache->getItem('foo_tag');
        $this->cache->saveDeferred($item);

        self::assertSpansCount(4);

        $mainSpan = self::getSpans()[3];
        self::assertSpanName($mainSpan, 'cache.save_deferred');
        self::assertSpanStatus($mainSpan, StatusData::unset());
        self::assertSpanAttributes($mainSpan, [
            'cache.item.save_deferred' => 'foo_tag',
        ]);
        self::assertSpanEventsCount($mainSpan, 0);
    }

    public function testCommit(): void
    {
        $item = $this->cache->getItem('foo_tag');
        $this->cache->saveDeferred($item);
        $this->cache->commit();

        self::assertSpansCount(7);

        $mainSpan = self::getSpans()[6];
        self::assertSpanName($mainSpan, 'cache.commit');
        self::assertSpanStatus($mainSpan, StatusData::unset());
        self::assertSpanAttributes($mainSpan, []);
        self::assertSpanEventsCount($mainSpan, 0);
    }

    public function testPrune(): void
    {
        $this->cache->prune();

        self::assertSpansCount(2);

        $mainSpan = self::getSpans()[1];
        self::assertSpanName($mainSpan, 'cache.prune');
        self::assertSpanStatus($mainSpan, StatusData::unset());
        self::assertSpanAttributes($mainSpan, []);
        self::assertSpanEventsCount($mainSpan, 0);
    }

    public function testReset(): void
    {
        $this->cache->reset();

        self::assertSpansCount(3);

        $mainSpan = self::getSpans()[2];
        self::assertSpanName($mainSpan, 'cache.reset');
        self::assertSpanStatus($mainSpan, StatusData::unset());
        self::assertSpanAttributes($mainSpan, []);
        self::assertSpanEventsCount($mainSpan, 0);
    }

    public function testInvalidateTags(): void
    {
        $this->cache->get('foo_tag', function (ItemInterface $item) {
            $item->tag('tag');

            return 'bar';
        });
        $this->cache->invalidateTags(['tag']);

        self::assertSpansCount(10);

        $mainSpan = self::getSpans()[9];
        self::assertSpanName($mainSpan, 'cache.invalidate_tags');
        self::assertSpanStatus($mainSpan, StatusData::unset());
        self::assertSpanAttributes($mainSpan, [
            'cache.invalidate_tags' => ['tag'],
        ]);
        self::assertSpanEventsCount($mainSpan, 0);
    }
}
