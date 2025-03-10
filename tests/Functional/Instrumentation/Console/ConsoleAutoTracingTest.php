<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Functional\Instrumentation\Console;

use App\Kernel;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Functional\TracingTestCaseTrait;
use OpenTelemetry\SDK\Trace\StatusData;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\ApplicationTester;
use Zalas\PHPUnit\Globals\Attribute\Env;

#[Env('KERNEL_CLASS', Kernel::class)]
#[Env('APP_ENV', 'auto')]
class ConsoleAutoTracingTest extends KernelTestCase
{
    use TracingTestCaseTrait;

    public function testSuccess(): void
    {
        $kernel = self::bootKernel();

        $application = new Application($kernel);
        $application->setAutoExit(false);

        $tester = new ApplicationTester($application);

        $tester->run(['command' => 'traceable:auto-command']);
        self::assertSame(0, $tester->getStatusCode());

        self::assertSpansCount(1);

        $mainSpan = self::getSpans()[0];
        self::assertSpanName($mainSpan, 'traceable:auto-command');
        self::assertSpanStatus($mainSpan, StatusData::ok());
        self::assertSpanAttributes($mainSpan, [
            'code.function.name' => 'execute',
            'code.namespace' => 'App\Command\Traceable\AutoCommand',
            'symfony.console.exit_code' => 0,
        ]);
        self::assertSpanEventsCount($mainSpan, 0);
    }

    public function testExcluded(): void
    {
        $kernel = self::bootKernel();

        $application = new Application($kernel);
        $application->setAutoExit(false);

        $tester = new ApplicationTester($application);

        $tester->run(['command' => 'traceable:dummy-command']);
        self::assertSame(0, $tester->getStatusCode());

        self::assertSpansCount(0);
    }
}
