<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Functional\Instrumentation\Console;

use App\Kernel;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Functional\MeterTestCaseTrait;
use OpenTelemetry\SDK\Metrics\Data\NumberDataPoint;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\ApplicationTester;
use Symfony\Component\VarDumper\Test\VarDumperTestTrait;
use Zalas\PHPUnit\Globals\Attribute\Env;

#[Env('KERNEL_CLASS', Kernel::class)]
#[Env('APP_ENV', 'auto')]
class ConsoleManualMeteringTest extends KernelTestCase
{
    use MeterTestCaseTrait;
    use VarDumperTestTrait;

    public function testSuccess(): void
    {
        $kernel = self::bootKernel();

        $application = new Application($kernel);
        $application->setAutoExit(false);

        $tester = new ApplicationTester($application);

        $tester->run(['command' => 'observable:manual-command']);
        self::assertSame(0, $tester->getStatusCode());

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
