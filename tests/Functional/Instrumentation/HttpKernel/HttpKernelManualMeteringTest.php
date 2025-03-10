<?php

declare(strict_types=1);

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Functional\Instrumentation\HttpKernel;

use App\Kernel;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Functional\MeterTestCaseTrait;
use OpenTelemetry\SDK\Metrics\Data\NumberDataPoint;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\VarDumper\Test\VarDumperTestTrait;
use Zalas\PHPUnit\Globals\Attribute\Env;

#[Env('KERNEL_CLASS', Kernel::class)]
#[Env('APP_ENV', 'auto')]
class HttpKernelManualMeteringTest extends WebTestCase
{
    use MeterTestCaseTrait;
    use VarDumperTestTrait;

    public function testManual(): void
    {
        $client = static::createClient();
        $client->request('GET', '/manual-observable');

        static::assertResponseIsSuccessful();
        static::assertSame('{"status":"ok"}', $client->getResponse()->getContent());

        self::assertMetricsCount(1);

        $metric = self::getMetrics()[0];

        self::assertMetricName($metric, 'manual');
        self::assertMetricDataInstanceOf(NumberDataPoint::class, $metric);
        self::assertDumpMatchesFormat(<<<DUMP
        OpenTelemetry\SDK\Metrics\Data\Sum {
          +dataPoints: array:1 [
            0 => OpenTelemetry\SDK\Metrics\Data\NumberDataPoint {
              +value: 1
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
}
