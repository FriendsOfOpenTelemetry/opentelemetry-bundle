<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\Sampler;

use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;
use OpenTelemetry\SDK\Trace\SamplerInterface;
use OpenTelemetry\SDK\Trace\SamplingResult;
use OpenTelemetry\SDK\Trace\Span;

class AttributeBasedSampler implements SamplerInterface
{
    public function __construct(
        private readonly string $attributeName,
        private readonly mixed $attributeValue = null,
    ) {
    }

    /**
     * @param AttributesInterface<mixed> $attributes
     */
    public function shouldSample(
        ContextInterface $parentContext,
        string $traceId,
        string $spanName,
        int $spanKind,
        AttributesInterface $attributes,
        array $links
    ): SamplingResult {
        $parentSpan = Span::fromContext($parentContext);
        $parentSpanContext = $parentSpan->getContext();
        $traceState = $parentSpanContext->getTraceState();

        if ($attributes->has($this->attributeName)) {
            $attributeValue = $attributes->get($this->attributeName);
            if (null !== $attributeValue && $attributeValue !== $this->attributeValue) {
                return new SamplingResult(SamplingResult::DROP, [], $traceState);
            }

            return new SamplingResult(SamplingResult::RECORD_AND_SAMPLE, [], $traceState);
        }

        return new SamplingResult(SamplingResult::DROP, [], $traceState);
    }

    public function getDescription(): string
    {
        return sprintf('AttributeBasedSampler{%s, %s}', $this->attributeName, $this->attributeValue);
    }
}
