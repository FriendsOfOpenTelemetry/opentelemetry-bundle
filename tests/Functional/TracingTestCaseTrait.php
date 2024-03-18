<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Functional;

use OpenTelemetry\SDK\Trace\EventInterface;
use OpenTelemetry\SDK\Trace\SpanDataInterface;
use OpenTelemetry\SDK\Trace\SpanExporter\InMemoryExporter;
use OpenTelemetry\SDK\Trace\StatusData;

trait TracingTestCaseTrait
{
    protected static function getSpanExporter(?string $exporterId = null): InMemoryExporter
    {
        $exporter = self::getContainer()->get($exporterId ?? 'open_telemetry.traces.exporters.in_memory');
        self::assertInstanceOf(InMemoryExporter::class, $exporter);

        return $exporter;
    }

    /**
     * @return SpanDataInterface[]
     */
    protected static function getSpans(?string $exporterId = null): array
    {
        return self::getSpanExporter($exporterId)->getSpans();
    }

    protected static function assertSpansCount(int $count, ?string $exporterId = null): void
    {
        $exporter = self::getSpanExporter($exporterId);
        self::assertCount($count, $exporter->getSpans());
    }

    protected static function assertSpanName(SpanDataInterface $spanData, string $name): void
    {
        self::assertSame($name, $spanData->getName());
    }

    protected static function assertSpanStatus(SpanDataInterface $spanData, StatusData $status): void
    {
        self::assertEquals($status, $spanData->getStatus());
    }

    /**
     * @param array<string, mixed> $attributes
     */
    protected static function assertSpanAttributes(SpanDataInterface $spanData, array $attributes): void
    {
        self::assertEquals($attributes, $spanData->getAttributes()->toArray());
    }

    /**
     * @param array<string, mixed> $attributes
     */
    protected static function assertSpanAttributesSubSet(SpanDataInterface $spanData, array $attributes): void
    {
        self::assertEquals($attributes, array_intersect_assoc($attributes, $spanData->getAttributes()->toArray()));
    }

    protected static function assertSpanEventsCount(SpanDataInterface $spanData, int $count): void
    {
        self::assertCount($count, $spanData->getEvents());
    }

    protected static function assertSpanEventName(EventInterface $event, string $name): void
    {
        self::assertSame($name, $event->getName());
    }

    /**
     * @param array<string, mixed> $attributes
     */
    protected static function assertSpanEventAttributes(EventInterface $event, array $attributes): void
    {
        self::assertSame($attributes, $event->getAttributes()->toArray());
    }

    /**
     * @param array<string, mixed> $attributes
     */
    protected static function assertSpanEventAttributesSubSet(EventInterface $event, array $attributes): void
    {
        self::assertSame($attributes, array_intersect_assoc($attributes, $event->getAttributes()->toArray()));
    }
}
