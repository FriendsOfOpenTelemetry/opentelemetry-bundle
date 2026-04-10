<?php

namespace App\Service;

use OpenTelemetry\API\Trace\StatusCode;
use OpenTelemetry\API\Trace\TracerInterface;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SemConv\Attributes\CodeAttributes;
use Psr\Log\LoggerInterface;

class DummyLoggerService
{
    public function __construct(
        private readonly TracerInterface $tracer,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function infoWithSpan(string $message): void
    {
        $parent = Context::getCurrent();

        $span = $this->tracer->spanBuilder('logWithSpan')
            ->setAttributes([
                CodeAttributes::CODE_FUNCTION_NAME => self::class.'::infoWithSpan',
            ])
            ->setParent($parent)
            ->startSpan();

        Context::storage()->attach($span->storeInContext($parent));

        $this->logger->info($message);

        $span->setStatus(StatusCode::STATUS_OK);
        $span->end();
    }
}
