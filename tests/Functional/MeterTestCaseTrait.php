<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Functional;

use OpenTelemetry\SDK\Metrics\Data\Metric;
use OpenTelemetry\SDK\Metrics\MetricExporter\InMemoryExporter;

trait MeterTestCaseTrait
{
    protected static function getMetricExporter(?string $exporterId = null): InMemoryExporter
    {
        $exporter = self::getContainer()->get($exporterId ?? 'open_telemetry.metrics.exporters.in_memory');
        self::assertInstanceOf(InMemoryExporter::class, $exporter);

        return $exporter;
    }

    protected static function shutdownMetrics(?string $providerId = null): void
    {
        $provider = self::getContainer()->get($providerId ?? 'open_telemetry.metrics.providers.default');
        $provider->shutdown();
    }

    /**
     * @return Metric[]
     */
    protected static function getMetrics(?string $exporterId = null): array
    {
        return self::getMetricExporter($exporterId)->collect();
    }

    protected static function assertMetricsCount(int $count, ?string $exporterId = null): void
    {
        $exporter = self::getMetricExporter($exporterId);
        self::assertCount($count, $exporter->collect());
    }

    protected static function assertMetricName(Metric $metric, string $name): void
    {
        self::assertSame($name, $metric->name);
    }

    /**
     * @param class-string $class
     */
    protected static function assertMetricDataInstanceOf(string $class, Metric $metric): void
    {
        self::assertObjectHasProperty('dataPoints', $metric->data);
        self::assertContainsOnlyInstancesOf($class, iterator_to_array($metric->data->dataPoints ?? []));
    }
}
