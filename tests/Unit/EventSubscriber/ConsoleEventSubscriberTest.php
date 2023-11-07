<?php

namespace GaelReyrol\OpenTelemetryBundle\Tests\Unit\EventSubscriber;

use GaelReyrol\OpenTelemetryBundle\EventSubscriber\ConsoleEventSubscriber;
use GaelReyrol\OpenTelemetryBundle\Tests\Application\Command\DummyCommand;
use OpenTelemetry\API\Trace\Span;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextStorageScopeInterface;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeFactory;
use OpenTelemetry\SDK\Resource\ResourceInfoFactory;
use OpenTelemetry\SDK\Trace\RandomIdGenerator;
use OpenTelemetry\SDK\Trace\Sampler\AlwaysOnSampler;
use OpenTelemetry\SDK\Trace\Sampler\ParentBased;
use OpenTelemetry\SDK\Trace\SpanLimitsBuilder;
use OpenTelemetry\SDK\Trace\StatusData;
use OpenTelemetry\SDK\Trace\Tracer;
use OpenTelemetry\SDK\Trace\TracerProviderInterface;
use OpenTelemetry\SDK\Trace\TracerSharedState;
use OpenTelemetry\SemConv\TraceAttributes;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

/**
 * @coversDefaultClass \GaelReyrol\OpenTelemetryBundle\EventSubscriber\ConsoleEventSubscriber
 */
final class ConsoleEventSubscriberTest extends TestCase
{
    private readonly MockObject&TracerProviderInterface $tracerProvider;

    protected function setUp(): void
    {
        $this->tracerProvider = $this->createMock(TracerProviderInterface::class);
        $this->tracerProvider
            ->expects(self::once())
            ->method('getTracer')
            ->with('gaelreyrol/opentelemetry-bundle', '0.0.0', TraceAttributes::SCHEMA_URL)
            ->willReturn(new Tracer(
                new TracerSharedState(
                    new RandomIdGenerator(),
                    ResourceInfoFactory::defaultResource(),
                    (new SpanLimitsBuilder())->build(),
                    new ParentBased(new AlwaysOnSampler()),
                    [],
                ),
                (new InstrumentationScopeFactory(Attributes::factory()))->create(
                    'gaelreyrol/opentelemetry-bundle',
                    '0.0.0',
                    TraceAttributes::SCHEMA_URL,
                ),
            ));
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

        $this->tracerProvider->shutdown();
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
        $subscriber = new ConsoleEventSubscriber($this->tracerProvider);

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
