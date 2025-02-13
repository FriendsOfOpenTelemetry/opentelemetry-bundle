<?php

declare(strict_types=1);

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Functional;

use App\Kernel;
use App\Service\DummyLoggerService;
use Monolog\Level;
use OpenTelemetry\SDK\Logs\ReadableLogRecord;
use OpenTelemetry\SDK\Trace\StatusData;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zalas\PHPUnit\Globals\Attribute\Env;

#[Env('KERNEL_CLASS', Kernel::class)]
#[CoversClass(DummyLoggerService::class)]
class DummyLoggerServiceTest extends KernelTestCase
{
    use LoggingTestCaseTrait;
    use TracingTestCaseTrait;

    private DummyLoggerService $dummyLoggerService;

    protected function setUp(): void
    {
        $this->dummyLoggerService = static::getContainer()->get(DummyLoggerService::class);
    }

    public function testInfoWithSpan(): void
    {
        $this->dummyLoggerService->infoWithSpan('Hello World!');

        $exporter = self::getLogExporter();

        self::assertCount(1, $exporter->getStorage());

        $log = self::getLogs()[0];

        self::assertInstanceOf(ReadableLogRecord::class, $log);
        self::assertSame(Level::Info->getName(), $log->getSeverityText());
        self::assertSame('Hello World!', $log->getBody());

        self::assertSpansCount(1);
        $span = self::getSpans()[0];

        self::assertSpanName($span, 'logWithSpan');
        self::assertSpanStatus($span, StatusData::ok());
        self::assertSpanAttributes($span, [
            'code.function.name' => 'logWithSpan',
            'code.namespace' => 'App\Service\DummyLoggerService',
        ]);

        self::assertSame($span->getSpanId(), $log->getSpanContext()->getSpanId());
    }
}
