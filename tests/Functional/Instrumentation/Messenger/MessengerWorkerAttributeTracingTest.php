<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Functional\Instrumentation\Messenger;

use App\Kernel;
use App\Message\DummyMessage;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Functional\TracingTestCaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Event\WorkerMessageHandledEvent;
use Symfony\Component\Messenger\Event\WorkerMessageReceivedEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Zalas\PHPUnit\Globals\Attribute\Env;

#[Env('KERNEL_CLASS', Kernel::class)]
class MessengerWorkerAttributeTracingTest extends KernelTestCase
{
    use TracingTestCaseTrait;

    private EventDispatcherInterface $eventDispatcher;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->eventDispatcher = self::getContainer()->get('event_dispatcher');
    }

    public function testNonAutoInstrumentationProducesNoSpans(): void
    {
        $envelope = new Envelope(new DummyMessage('test'));

        $this->eventDispatcher->dispatch(new WorkerMessageReceivedEvent($envelope, 'main'));
        $this->eventDispatcher->dispatch(new WorkerMessageHandledEvent($envelope, 'main'));

        self::assertSpansCount(0);
    }
}
