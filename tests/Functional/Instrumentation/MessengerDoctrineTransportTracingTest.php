<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Functional\Instrumentation;

use App\Kernel;
use App\Message\DummyMessage;
use App\Message\ExceptionMessage;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Functional\TracingTestCaseTrait;
use OpenTelemetry\SDK\Trace\SpanDataInterface;
use OpenTelemetry\SDK\Trace\StatusData;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Messenger\MessageBusInterface;
use Zalas\PHPUnit\Globals\Attribute\Env;

#[Env('KERNEL_CLASS', Kernel::class)]
#[Env('APP_ENV', 'messenger_doctrine_transport')]
class MessengerDoctrineTransportTracingTest extends KernelTestCase
{
    use TracingTestCaseTrait;

    private MessageBusInterface $bus;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->bus = self::getContainer()->get('messenger.bus.default');
    }

    public function testDispatch(): void
    {
        $this->bus->dispatch(new DummyMessage('test'));

        self::assertSpansCount(7);

        $spans = self::getSpans();
        $spanNames = array_map(static fn (SpanDataInterface $span) => $span->getName(), $spans);

        self::assertEquals([
            'doctrine.dbal.connection',
            'doctrine.dbal.transaction.begin',
            'doctrine.dbal.statement.prepare',
            'doctrine.dbal.statement.execute',
            'doctrine.dbal.transaction.commit',
            'messenger.transport.send',
            'messenger.middleware',
        ], $spanNames);

        $transportSpan = $spans[5];
        self::assertSpanName($transportSpan, 'messenger.transport.send');
        self::assertSpanStatus($transportSpan, StatusData::unset());
        self::assertSpanAttributes($transportSpan, []);
        self::assertSpanEventsCount($transportSpan, 0);

        $middlewareSpan = $spans[6];
        self::assertSpanName($middlewareSpan, 'messenger.middleware');
        self::assertSpanStatus($middlewareSpan, StatusData::ok());
        self::assertSpanAttributes($middlewareSpan, [
            'event.category' => 'messenger.middleware',
            'bus.name' => 'default',
            'event.current' => '"Symfony\Component\Messenger\Middleware\SendMessageMiddleware" on "default"',
        ]);
        self::assertSpanEventsCount($middlewareSpan, 0);
    }

    public function testException(): void
    {
        $this->bus->dispatch(new ExceptionMessage('test'));

        self::assertSpansCount(7);

        $spans = self::getSpans();
        $spanNames = array_map(static fn (SpanDataInterface $span) => $span->getName(), $spans);

        self::assertEquals([
            'doctrine.dbal.connection',
            'doctrine.dbal.transaction.begin',
            'doctrine.dbal.statement.prepare',
            'doctrine.dbal.statement.execute',
            'doctrine.dbal.transaction.commit',
            'messenger.transport.send',
            'messenger.middleware',
        ], $spanNames);

        $transportSpan = $spans[5];
        self::assertSpanName($transportSpan, 'messenger.transport.send');
        self::assertSpanStatus($transportSpan, StatusData::unset());
        self::assertSpanAttributes($transportSpan, []);
        self::assertSpanEventsCount($transportSpan, 0);

        $middlewareSpan = $spans[6];
        self::assertSpanName($middlewareSpan, 'messenger.middleware');
        self::assertSpanStatus($middlewareSpan, StatusData::ok());
        self::assertSpanAttributes($middlewareSpan, [
            'event.category' => 'messenger.middleware',
            'bus.name' => 'default',
            'event.current' => '"Symfony\Component\Messenger\Middleware\SendMessageMiddleware" on "default"',
        ]);
        self::assertSpanEventsCount($middlewareSpan, 0);
    }
}
