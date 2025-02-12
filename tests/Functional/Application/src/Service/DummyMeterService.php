<?php

namespace App\Service;

use OpenTelemetry\API\Metrics\MeterInterface;

class DummyMeterService
{
    public function __construct(
        private readonly MeterInterface $meter,
    ) {
    }

    /**
     * @param int[] $values
     */
    public function count(array $values): void
    {
        $counter = $this->meter->createCounter('dummy');

        array_map(static fn (int $value) => $counter->add($value), $values);
    }

    /**
     * @param int[] $values
     */
    public function gauge(array $values): void
    {
        $gauge = $this->meter->createGauge('dummy');

        array_map(static fn (int $value) => $gauge->record($value), $values);
    }

    /**
     * @param int[] $values
     */
    public function upDownCount(array $values): void
    {
        $upDownCounter = $this->meter->createUpDownCounter('dummy');

        array_map(static fn (int $value) => $upDownCounter->add($value), $values);
    }

    /**
     * @param int[] $values
     */
    public function histogram(array $values): void
    {
        $histogram = $this->meter->createHistogram('dummy');

        array_map(static fn (int $value) => $histogram->record($value), $values);
    }
}
