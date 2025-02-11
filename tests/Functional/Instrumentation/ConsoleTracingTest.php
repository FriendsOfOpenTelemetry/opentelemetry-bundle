<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Functional\Instrumentation;

use App\Kernel;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Functional\TracingTestCaseTrait;
use OpenTelemetry\SDK\Trace\SpanExporter\InMemoryExporter;
use OpenTelemetry\SDK\Trace\StatusData;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\ApplicationTester;
use Zalas\PHPUnit\Globals\Attribute\Env;

#[Env('KERNEL_CLASS', Kernel::class)]
class ConsoleTracingTest extends KernelTestCase
{
    use TracingTestCaseTrait;

    public function testSuccess(): void
    {
        $kernel = self::bootKernel();

        $application = new Application($kernel);
        $application->setAutoExit(false);

        $tester = new ApplicationTester($application);

        $tester->run(['command' => 'traceable-command']);
        $tester->assertCommandIsSuccessful();

        self::assertSpansCount(1);

        $mainSpan = self::getSpans()[0];
        self::assertSpanName($mainSpan, 'traceable-command');
        self::assertSpanStatus($mainSpan, StatusData::ok());
        self::assertSpanAttributes($mainSpan, [
            'code.function.name' => 'execute',
            'code.namespace' => 'App\Command\TraceableCommand',
            'symfony.console.exit_code' => 0,
        ]);
        self::assertSpanEventsCount($mainSpan, 0);
    }

    public function testFailure(): void
    {
        $kernel = self::bootKernel();

        $application = new Application($kernel);
        $application->setAutoExit(false);

        $tester = new ApplicationTester($application);

        $tester->run([
            'command' => 'traceable-command',
            '--fail' => true,
        ]);
        self::assertSame(1, $tester->getStatusCode());

        self::assertSpansCount(1);

        $mainSpan = self::getSpans()[0];
        self::assertSpanName($mainSpan, 'traceable-command');
        self::assertSpanStatus($mainSpan, StatusData::error());
        self::assertSpanAttributes($mainSpan, [
            'code.function.name' => 'execute',
            'code.namespace' => 'App\Command\TraceableCommand',
            'symfony.console.exit_code' => 1,
        ]);
        self::assertSpanEventsCount($mainSpan, 0);
    }

    public function testException(): void
    {
        $kernel = self::bootKernel();

        $application = new Application($kernel);
        $application->setAutoExit(false);

        $tester = new ApplicationTester($application);

        $tester->run([
            'command' => 'traceable-command',
            '--throw' => true,
        ], [
            'verbosity' => OutputInterface::VERBOSITY_QUIET,
        ]);
        self::assertSame(1, $tester->getStatusCode());

        $exporter = self::getContainer()->get('open_telemetry.traces.exporters.in_memory');
        self::assertInstanceOf(InMemoryExporter::class, $exporter);

        self::assertSpansCount(1);

        $mainSpan = self::getSpans()[0];
        self::assertSpanName($mainSpan, 'traceable-command');
        self::assertSpanStatus($mainSpan, StatusData::error());
        self::assertSpanAttributes($mainSpan, [
            'code.function.name' => 'execute',
            'code.namespace' => 'App\Command\TraceableCommand',
            'symfony.console.exit_code' => 1,
        ]);

        self::assertSpanEventsCount($mainSpan, 1);

        $exception = $mainSpan->getEvents()[0];
        self::assertSpanEventName($exception, 'exception');
        self::assertSpanEventAttributesSubSet($exception, [
            'exception.type' => 'RuntimeException',
            'exception.message' => 'Oops',
            'symfony.console.exit_code' => 1,
        ]);
    }

    public function testFallback(): void
    {
        $kernel = self::bootKernel();

        $application = new Application($kernel);
        $application->setAutoExit(false);

        $tester = new ApplicationTester($application);

        $tester->run(['command' => 'fallback-command']);
        $tester->assertCommandIsSuccessful();

        self::assertSpansCount(0);

        self::assertSpansCount(1, 'open_telemetry.traces.exporters.fallback');

        $mainSpan = self::getSpans('open_telemetry.traces.exporters.fallback')[0];
        self::assertSpanName($mainSpan, 'fallback-command');
        self::assertSpanStatus($mainSpan, StatusData::ok());
        self::assertSpanAttributes($mainSpan, [
            'code.function.name' => 'execute',
            'code.namespace' => 'App\Command\FallbackCommand',
            'symfony.console.exit_code' => 0,
        ]);
        self::assertSpanEventsCount($mainSpan, 0);
    }
}
