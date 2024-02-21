<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric;

use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilter\AllExemplarFilter;
use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilter\NoneExemplarFilter;
use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilter\WithSampledTraceExemplarFilter;
use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilterInterface;

final class ExemplarFilterFactory
{
    public function create(string $name): ExemplarFilterInterface
    {
        $filter = ExemplarFilterEnum::tryFrom($name);

        return match ($filter) {
            ExemplarFilterEnum::All => new AllExemplarFilter(),
            ExemplarFilterEnum::None => new NoneExemplarFilter(),
            ExemplarFilterEnum::WithSampledTrace => new WithSampledTraceExemplarFilter(),
            default => throw new \InvalidArgumentException(sprintf('Unknown exemplar filter: %s', $name)),
        };
    }
}
