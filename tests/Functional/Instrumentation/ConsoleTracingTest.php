<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Functional\Instrumentation;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Application\Kernel;
use OpenTelemetry\SDK\Trace\Event;
use OpenTelemetry\SDK\Trace\SpanDataInterface;
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
    public function testSuccess(): void
    {
        $kernel = self::bootKernel();

        $application = new Application($kernel);
        $application->setAutoExit(false);

        $tester = new ApplicationTester($application);

        $tester->run(['command' => 'dummy-command']);
        $tester->assertCommandIsSuccessful();

        $exporter = self::getContainer()->get('open_telemetry.traces.exporters.in_memory');
        self::assertInstanceOf(InMemoryExporter::class, $exporter);

        $spans = $exporter->getSpans();
        self::assertContainsOnlyInstancesOf(SpanDataInterface::class, $spans);
        self::assertCount(1, $spans);

        /** @var SpanDataInterface $mainSpan */
        $mainSpan = $spans[0];
        self::assertSame('dummy-command', $mainSpan->getName());
        self::assertSame(StatusData::ok(), $mainSpan->getStatus());
        self::assertSame([
            'code.function' => 'execute',
            'code.namespace' => 'FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Application\Command\DummyCommand',
            'symfony.console.exit_code' => 0,
        ], $mainSpan->getAttributes()->toArray());
    }

    public function testFailure(): void
    {
        $kernel = self::bootKernel();

        $application = new Application($kernel);
        $application->setAutoExit(false);

        $tester = new ApplicationTester($application);

        $tester->run([
            'command' => 'dummy-command',
            '--fail' => true,
        ]);
        self::assertSame(1, $tester->getStatusCode());

        $exporter = self::getContainer()->get('open_telemetry.traces.exporters.in_memory');
        self::assertInstanceOf(InMemoryExporter::class, $exporter);

        $spans = $exporter->getSpans();
        self::assertContainsOnlyInstancesOf(SpanDataInterface::class, $spans);
        self::assertCount(1, $spans);

        /** @var SpanDataInterface $mainSpan */
        $mainSpan = $spans[0];
        self::assertSame('dummy-command', $mainSpan->getName());
        self::assertSame(StatusData::error(), $mainSpan->getStatus());
        self::assertSame([
            'code.function' => 'execute',
            'code.namespace' => 'FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Application\Command\DummyCommand',
            'symfony.console.exit_code' => 1,
        ], $mainSpan->getAttributes()->toArray());
    }

    public function testException(): void
    {
        $kernel = self::bootKernel();

        $application = new Application($kernel);
        $application->setAutoExit(false);

        $tester = new ApplicationTester($application);

        $tester->run([
            'command' => 'dummy-command',
            '--throw' => true,
        ], [
            'verbosity' => OutputInterface::VERBOSITY_QUIET,
        ]);
        self::assertSame(1, $tester->getStatusCode());

        $exporter = self::getContainer()->get('open_telemetry.traces.exporters.in_memory');
        self::assertInstanceOf(InMemoryExporter::class, $exporter);

        $spans = $exporter->getSpans();
        self::assertContainsOnlyInstancesOf(SpanDataInterface::class, $spans);
        self::assertCount(1, $spans);

        /** @var SpanDataInterface $mainSpan */
        $mainSpan = $spans[0];
        self::assertSame('dummy-command', $mainSpan->getName());
        self::assertSame(StatusData::error(), $mainSpan->getStatus());
        self::assertSame([
            'code.function' => 'execute',
            'code.namespace' => 'FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Application\Command\DummyCommand',
            'symfony.console.exit_code' => 1,
        ], $mainSpan->getAttributes()->toArray());

        $events = $mainSpan->getEvents();
        self::assertCount(1, $events);
        self::assertContainsOnlyInstancesOf(Event::class, $events);

        $exception = $events[0];
        self::assertSame('exception', $exception->getName());
        $exceptionAttributes = [
            'exception.type' => 'RuntimeException',
            'exception.message' => 'Oops',
            'symfony.console.exit_code' => 1,
        ];
        self::assertSame($exceptionAttributes, array_intersect_assoc($exceptionAttributes, $exception->getAttributes()->toArray()));
    }
}
