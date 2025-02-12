<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric;

use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilter\AllExemplarFilter;
use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilter\NoneExemplarFilter;
use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilter\WithSampledTraceExemplarFilter;
use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilterInterface;

final class ExemplarFilterFactory
{
    /**
     * @param array{service_id?: mixed} $params
     */
    public static function create(string $name, array $params = []): ExemplarFilterInterface
    {
        $filter = ExemplarFilterEnum::tryFrom($name);

        if (isset($params['service_id']) && false === $params['service_id'] instanceof ExemplarFilterInterface) {
            throw new \InvalidArgumentException('Parameter service_id must be an instance of ExemplarFilterInterface');
        }

        return match ($filter) {
            ExemplarFilterEnum::All => new AllExemplarFilter(),
            ExemplarFilterEnum::None => new NoneExemplarFilter(),
            ExemplarFilterEnum::WithSampledTrace => new WithSampledTraceExemplarFilter(),
            ExemplarFilterEnum::Service => $params['service_id'],
            default => throw new \InvalidArgumentException(sprintf('Unknown exemplar filter: %s', $name)),
        };
    }
}
