<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\EventSubscriber;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Context\Attribute\ConsoleTraceAttributeEnum;
use OpenTelemetry\API\Trace\Span;
use OpenTelemetry\API\Trace\StatusCode;
use OpenTelemetry\API\Trace\TracerInterface;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SemConv\TraceAttributes;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Event\ConsoleErrorEvent;
use Symfony\Component\Console\Event\ConsoleSignalEvent;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class ConsoleTraceEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly TracerInterface $tracer,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ConsoleEvents::COMMAND => [
                ['startSpan', 10000],
            ],
            ConsoleEvents::ERROR => [
                ['handleError', -10000],
            ],
            ConsoleEvents::TERMINATE => [
                ['endSpan', -10000],
            ],
            ConsoleEvents::SIGNAL => [
                ['handleSignal', -10000],
            ],
        ];
    }

    public function startSpan(ConsoleCommandEvent $event): void
    {
        $command = $event->getCommand();

        assert($command instanceof Command);

        $name = $command->getName();
        $class = get_class($command);

        $spanBuilder = $this->tracer
            ->spanBuilder($name)
            ->setAttributes([
                TraceAttributes::CODE_FUNCTION => 'execute',
                TraceAttributes::CODE_NAMESPACE => $class,
            ]);

        $parent = Context::getCurrent();

        $span = $spanBuilder->setParent($parent)->startSpan();

        Context::storage()->attach($span->storeInContext($parent));
    }

    public function handleError(ConsoleErrorEvent $event): void
    {
        $span = Span::getCurrent();
        $span->setStatus(StatusCode::STATUS_ERROR);
        $span->recordException($event->getError(), [
            ConsoleTraceAttributeEnum::ExitCode->value => $event->getExitCode(),
        ]);
    }

    public function endSpan(ConsoleTerminateEvent $event): void
    {
        $scope = Context::storage()->scope();
        if (null === $scope) {
            return;
        }
        $scope->detach();

        $span = Span::fromContext($scope->context());
        $span->setAttribute(
            ConsoleTraceAttributeEnum::ExitCode->value,
            $event->getExitCode()
        );

        $statusCode = match ($event->getExitCode()) {
            Command::SUCCESS => StatusCode::STATUS_OK,
            default => StatusCode::STATUS_ERROR,
        };
        $span->setStatus($statusCode);

        $span->end();
    }

    public function handleSignal(ConsoleSignalEvent $event): void
    {
        $span = Span::getCurrent();
        $span->setAttribute(ConsoleTraceAttributeEnum::SignalCode->value, $event->getHandlingSignal());
    }
}
