<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Twig;

use OpenTelemetry\API\Trace\SpanInterface;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\API\Trace\TracerInterface;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ScopeInterface;
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
    ) {
        $this->spans = new \SplObjectStorage();
    }

    public function enter(Profile $profile): void
    {
        $scope = Context::storage()->scope();

        $spanBuilder = $this->tracer
            ->spanBuilder($this->getSpanName($profile))
            ->setSpanKind(SpanKind::KIND_INTERNAL)
        ;

        $parent = Context::getCurrent();

        $span = $spanBuilder->setParent($parent)->startSpan();
        if (null === $scope) {
            $this->scope = $span->storeInContext($parent)->activate();
        }

        $this->spans[$profile] = $span;
    }

    public function leave(Profile $profile): void
    {
        $scope = Context::storage()->scope() ?? $this->scope;
        if (null === $scope) {
            return;
        }

        if (!isset($this->spans[$profile])) {
            return;
        }
        if (null !== $this->scope && 1 === count($this->spans)) {
            $this->scope->detach();
            $this->scope = null;
        }
        $this->spans[$profile]->end();
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
