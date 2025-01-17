<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Functional\Instrumentation;

use App\Kernel;
use App\Message\DummyMessage;
use App\Message\ExceptionMessage;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Functional\TracingTestCaseTrait;
use OpenTelemetry\SDK\Trace\StatusData;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Messenger\MessageBusInterface;
use Zalas\PHPUnit\Globals\Attribute\Env;

#[Env('KERNEL_CLASS', Kernel::class)]
class MessengerTracingTest extends KernelTestCase
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

        self::assertSpansCount(2);

        $transportSpan = self::getSpans()[0];
        self::assertSpanName($transportSpan, 'messenger.transport.send');
        self::assertSpanStatus($transportSpan, StatusData::unset());
        self::assertSpanAttributes($transportSpan, []);
        self::assertSpanEventsCount($transportSpan, 0);

        $middlewareSpan = self::getSpans()[1];
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

        self::assertSpansCount(2);

        $transportSpan = self::getSpans()[0];
        self::assertSpanName($transportSpan, 'messenger.transport.send');
        self::assertSpanStatus($transportSpan, StatusData::unset());
        self::assertSpanAttributes($transportSpan, []);
        self::assertSpanEventsCount($transportSpan, 0);

        $middlewareSpan = self::getSpans()[1];
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
