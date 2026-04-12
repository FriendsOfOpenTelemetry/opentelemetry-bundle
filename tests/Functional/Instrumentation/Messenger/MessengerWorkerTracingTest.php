<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Functional\Instrumentation\Messenger;

use App\Kernel;
use App\Message\DummyMessage;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Functional\TracingTestCaseTrait;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\SDK\Trace\StatusData;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;
use Symfony\Component\Messenger\Event\WorkerMessageHandledEvent;
use Symfony\Component\Messenger\Event\WorkerMessageReceivedEvent;
use Symfony\Component\Messenger\Stamp\BusNameStamp;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Zalas\PHPUnit\Globals\Attribute\Env;

#[Env('KERNEL_CLASS', Kernel::class)]
#[Env('APP_ENV', 'auto')]
class MessengerWorkerTracingTest extends KernelTestCase
{
    use TracingTestCaseTrait;

    private EventDispatcherInterface $eventDispatcher;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->eventDispatcher = self::getContainer()->get('event_dispatcher');
    }

    public function testWorkerMessageHandled(): void
    {
        $envelope = new Envelope(new DummyMessage('test'), [new BusNameStamp('messenger.bus.default')]);

        $this->eventDispatcher->dispatch(new WorkerMessageReceivedEvent($envelope, 'main'));
        $this->eventDispatcher->dispatch(new WorkerMessageHandledEvent($envelope, 'main'));

        self::assertSpansCount(1);

        $span = self::getSpans()[0];
        self::assertSpanName($span, 'main App\Message\DummyMessage');
        self::assertSpanStatus($span, StatusData::ok());
        self::assertSame(SpanKind::KIND_CONSUMER, $span->getKind());
        self::assertSpanAttributesSubSet($span, [
            'messaging.operation.type' => 'process',
            'messaging.destination.name' => 'main',
            'bus.name' => 'messenger.bus.default',
        ]);
        self::assertSpanEventsCount($span, 0);
    }

    public function testWorkerMessageHandledWithoutBusNameStamp(): void
    {
        $envelope = new Envelope(new DummyMessage('test'));

        $this->eventDispatcher->dispatch(new WorkerMessageReceivedEvent($envelope, 'main'));
        $this->eventDispatcher->dispatch(new WorkerMessageHandledEvent($envelope, 'main'));

        self::assertSpansCount(1);

        $span = self::getSpans()[0];
        self::assertSpanName($span, 'main App\Message\DummyMessage');
        self::assertSpanStatus($span, StatusData::ok());
        self::assertSpanAttributes($span, [
            'messaging.operation.type' => 'process',
            'messaging.destination.name' => 'main',
        ]);
    }

    public function testWorkerMessageFailed(): void
    {
        $envelope = new Envelope(new DummyMessage('test'), [new BusNameStamp('messenger.bus.default')]);
        $exception = new \RuntimeException('Something went wrong');

        $this->eventDispatcher->dispatch(new WorkerMessageReceivedEvent($envelope, 'main'));
        $this->eventDispatcher->dispatch(new WorkerMessageFailedEvent($envelope, 'main', $exception));

        self::assertSpansCount(1);

        $span = self::getSpans()[0];
        self::assertSpanName($span, 'main App\Message\DummyMessage');
        self::assertSpanStatus($span, StatusData::error());
        self::assertSame(SpanKind::KIND_CONSUMER, $span->getKind());
        self::assertSpanAttributesSubSet($span, [
            'messaging.operation.type' => 'process',
            'messaging.destination.name' => 'main',
            'bus.name' => 'messenger.bus.default',
            'exception.message' => 'Something went wrong',
        ]);
    }

    public function testWorkerMessageFailedWithPreviousException(): void
    {
        $envelope = new Envelope(new DummyMessage('test'));
        $previous = new \LogicException('Root cause');
        $exception = new \RuntimeException('Something went wrong', 0, $previous);

        $this->eventDispatcher->dispatch(new WorkerMessageReceivedEvent($envelope, 'main'));
        $this->eventDispatcher->dispatch(new WorkerMessageFailedEvent($envelope, 'main', $exception));

        self::assertSpansCount(1);

        $span = self::getSpans()[0];
        self::assertSpanStatus($span, StatusData::error());
        self::assertSpanAttributesSubSet($span, [
            'exception.message' => 'Something went wrong',
            'exception.previous.message' => 'Root cause',
        ]);
    }
}
