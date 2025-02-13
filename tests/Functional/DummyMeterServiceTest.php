<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Functional;

use App\Kernel;
use App\Service\DummyMeterService;
use OpenTelemetry\SDK\Metrics\Data\HistogramDataPoint;
use OpenTelemetry\SDK\Metrics\Data\NumberDataPoint;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\VarDumper\Test\VarDumperTestTrait;
use Zalas\PHPUnit\Globals\Attribute\Env;

#[Env('KERNEL_CLASS', Kernel::class)]
#[CoversClass(DummyMeterService::class)]
final class DummyMeterServiceTest extends KernelTestCase
{
    use MeterTestCaseTrait;
    use VarDumperTestTrait;

    private DummyMeterService $meterService;

    protected function setUp(): void
    {
        $this->meterService = self::getContainer()->get(DummyMeterService::class);
    }

    public function testCount(): void
    {
        $this->meterService->count([21, 21]);
        $this->meterService->count([21, 21]);
        self::shutdownMetrics();

        self::assertMetricsCount(1);

        $metric = self::getMetrics()[0];

        self::assertMetricName($metric, 'dummy');
        self::assertMetricDataInstanceOf(NumberDataPoint::class, $metric);

        self::assertDumpMatchesFormat(<<<DUMP
        OpenTelemetry\SDK\Metrics\Data\Sum {
          +dataPoints: array:1 [
            0 => OpenTelemetry\SDK\Metrics\Data\NumberDataPoint {
              +value: 84
              +attributes: OpenTelemetry\SDK\Common\Attribute\Attributes {
                -attributes: []
                -droppedAttributesCount: 0
              }
              +startTimestamp: %d
              +timestamp: %d
              +exemplars: []
            }
          ]
          +temporality: "Delta"
          +monotonic: true
        }
        DUMP, $metric->data);
    }

    public function testGauge(): void
    {
        $this->meterService->gauge([21, 21]);
        self::shutdownMetrics();

        self::assertMetricsCount(1);

        $metric = self::getMetrics()[0];

        self::assertMetricName($metric, 'dummy');
        self::assertMetricDataInstanceOf(NumberDataPoint::class, $metric);

        self::assertDumpMatchesFormat(<<<DUMP
        OpenTelemetry\SDK\Metrics\Data\Gauge {
          +dataPoints: array:1 [
            0 => OpenTelemetry\SDK\Metrics\Data\NumberDataPoint {
              +value: 21
              +attributes: OpenTelemetry\SDK\Common\Attribute\Attributes {
                -attributes: []
                -droppedAttributesCount: 0
              }
              +startTimestamp: %d
              +timestamp: %d
              +exemplars: []
            }
          ]
        }
        DUMP, $metric->data);
    }

    public function testUpDown(): void
    {
        $this->meterService->upDownCount([21, -21]);
        $this->meterService->upDownCount([-21, 21]);
        self::shutdownMetrics();

        self::assertMetricsCount(1);

        $metric = self::getMetrics()[0];

        self::assertMetricName($metric, 'dummy');
        self::assertMetricDataInstanceOf(NumberDataPoint::class, $metric);

        self::assertDumpMatchesFormat(<<<DUMP
        OpenTelemetry\SDK\Metrics\Data\Sum {
          +dataPoints: array:1 [
            0 => OpenTelemetry\SDK\Metrics\Data\NumberDataPoint {
              +value: 0
              +attributes: OpenTelemetry\SDK\Common\Attribute\Attributes {
                -attributes: []
                -droppedAttributesCount: 0
              }
              +startTimestamp: %d
              +timestamp: %d
              +exemplars: []
            }
          ]
          +temporality: "Delta"
          +monotonic: false
        }
        DUMP, $metric->data);
    }

    public function testHistogram(): void
    {
        $this->meterService->histogram([21, 42]);
        $this->meterService->histogram([84, 42]);
        self::shutdownMetrics();

        self::assertMetricsCount(1);

        $metric = self::getMetrics()[0];

        self::assertMetricName($metric, 'dummy');
        self::assertMetricDataInstanceOf(HistogramDataPoint::class, $metric);

        self::assertDumpMatchesFormat(<<<DUMP
        OpenTelemetry\SDK\Metrics\Data\Histogram {
          +dataPoints: array:1 [
            0 => OpenTelemetry\SDK\Metrics\Data\HistogramDataPoint {
              +count: 4
              +sum: 189
              +min: 21
              +max: 84
              +bucketCounts: array:11 [
                0 => 0
                1 => 0
                2 => 0
                3 => 1
                4 => 2
                5 => 0
                6 => 1
                7 => 0
                8 => 0
                9 => 0
                10 => 0
              ]
              +explicitBounds: array:10 [
                0 => 0
                1 => 5
                2 => 10
                3 => 25
                4 => 50
                5 => 75
                6 => 100
                7 => 250
                8 => 500
                9 => 1000
              ]
              +attributes: OpenTelemetry\SDK\Common\Attribute\Attributes {
                -attributes: []
                -droppedAttributesCount: 0
              }
              +startTimestamp: %d
              +timestamp: %d
              +exemplars: []
            }
          ]
          +temporality: "Delta"
        }
        DUMP, $metric->data);
    }
}
