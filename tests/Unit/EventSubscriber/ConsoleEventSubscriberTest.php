<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Unit\EventSubscriber;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Symfony\EventSubscriber\Console\TraceableConsoleEventSubscriber;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Application\Command\DummyCommand;
use OpenTelemetry\API\Trace\Span;
use OpenTelemetry\API\Trace\TracerInterface;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextStorageScopeInterface;
use OpenTelemetry\SDK\Trace\StatusData;
use OpenTelemetry\SDK\Trace\TracerProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

/**
 * @coversDefaultClass \FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Symfony\EventSubscriber\Console\TraceableConsoleEventSubscriber
 */
final class ConsoleEventSubscriberTest extends TestCase
{
    private readonly TracerInterface $tracer;

    protected function setUp(): void
    {
        $this->tracer = (new TracerProvider())->getTracer('friendsofopentelemetry/opentelemetry-bundle');
    }

    protected function tearDown(): void
    {
        $scope = Context::storage()->scope();
        if (!$scope instanceof ContextStorageScopeInterface) {
            return;
        }
        $scope->detach();

        $span = Span::fromContext($scope->context());
        $span->end();
    }

    /**
     * @return array<int, array{0: ConsoleCommandEvent}>
     */
    public function consoleCommandEventDataProvider(): array
    {
        return [
            [new ConsoleCommandEvent(new DummyCommand(), new ArrayInput([]), new NullOutput())],
        ];
    }

    /**
     * @dataProvider consoleCommandEventDataProvider
     */
    public function testHandleCommandEvent(ConsoleCommandEvent $event): void
    {
        $subscriber = new TraceableConsoleEventSubscriber($this->tracer);

        $subscriber->startSpan($event);

        $span = Span::getCurrent();
        assert($span instanceof \OpenTelemetry\SDK\Trace\Span);

        $command = $event->getCommand();
        assert($command instanceof Command);

        $data = $span->toSpanData();
        self::assertSame($command->getName(), $data->getName());
        self::assertSame([
            'code.function' => 'execute',
            'code.namespace' => get_class($command),
        ], $data->getAttributes()->toArray());
        self::assertSame(StatusData::unset(), $data->getStatus());
    }
}
