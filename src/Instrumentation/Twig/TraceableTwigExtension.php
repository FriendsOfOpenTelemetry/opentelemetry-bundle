<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Twig;

use OpenTelemetry\API\Trace\SpanInterface;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\API\Trace\TracerInterface;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ScopeInterface;
use Psr\Log\LoggerInterface;
use Twig\Extension\AbstractExtension;
use Twig\Profiler\NodeVisitor\ProfilerNodeVisitor;
use Twig\Profiler\Profile;

class TraceableTwigExtension extends AbstractExtension
{
    /**
     * @var \SplObjectStorage<Profile, SpanInterface>
     */
    private \SplObjectStorage $spans;

    private ?ScopeInterface $scope = null;

    public function __construct(
        private readonly TracerInterface $tracer,
        private readonly ?LoggerInterface $logger = null,
    ) {
        $this->spans = new \SplObjectStorage();
    }

    public function enter(Profile $profile): void
    {
        $scope = Context::storage()->scope();
        if (null !== $scope) {
            $this->logger?->debug(sprintf('Using scope "%s"', spl_object_id($scope)));
        }

        $spanBuilder = $this->tracer
            ->spanBuilder($this->getSpanName($profile))
            ->setSpanKind(SpanKind::KIND_INTERNAL)
        ;

        $span = $spanBuilder->setParent($scope?->context())->startSpan();

        $this->logger?->debug(sprintf('Starting span "%s"', $span->getContext()->getSpanId()));

        if (null === $scope && null === $this->scope) {
            $this->scope = $span->storeInContext(Context::getCurrent())->activate();
            $this->logger?->debug(sprintf('No active scope, activating new scope "%s"', spl_object_id($this->scope)));
        }

        $this->spans[$profile] = $span;
    }

    public function leave(Profile $profile): void
    {
        if (!isset($this->spans[$profile])) {
            return;
        }

        if (null !== $this->scope && 1 === count($this->spans)) {
            $this->logger?->debug(sprintf('Detaching scope "%s"', spl_object_id($this->scope)));
            $this->scope->detach();
            $this->scope = null;
        }

        $span = $this->spans[$profile];
        $this->logger?->debug(sprintf('Ending span "%s"', $span->getContext()->getSpanId()));
        $span->end();
        unset($this->spans[$profile]);
    }

    public function getNodeVisitors(): array
    {
        return [
            new ProfilerNodeVisitor(self::class),
        ];
    }

    private function getSpanName(Profile $profile): string
    {
        switch (true) {
            case $profile->isRoot():
                return $profile->getName();

            case $profile->isTemplate():
                return $profile->getTemplate();

            default:
                return sprintf('%s::%s(%s)', $profile->getTemplate(), $profile->getType(), $profile->getName());
        }
    }
}
