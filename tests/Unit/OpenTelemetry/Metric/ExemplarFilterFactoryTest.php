<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Unit\OpenTelemetry\Metric;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\ExemplarFilterEnum;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\ExemplarFilterFactory;
use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilter\AllExemplarFilter;
use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilter\NoneExemplarFilter;
use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilter\WithSampledTraceExemplarFilter;
use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilterInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(ExemplarFilterFactory::class)]
class ExemplarFilterFactoryTest extends TestCase
{
    #[DataProvider('exemplarFilter')]
    public function testCreate(string $name, ?string $class): void
    {
        if (null === $class) {
            self::expectExceptionObject(
                new \InvalidArgumentException(sprintf('Unknown exemplar filter: %s', $name)),
            );
        }

        self::assertInstanceOf($class, ExemplarFilterFactory::create($name));
    }

    /**
     * @return \Generator{string, array{
     *     string,
     *     ?class-string<ExemplarFilterInterface>,
     * }}
     */
    public static function exemplarFilter(): \Generator
    {
        yield 'all' => [
            ExemplarFilterEnum::All->value,
            AllExemplarFilter::class,
        ];

        yield 'none' => [
            ExemplarFilterEnum::None->value,
            NoneExemplarFilter::class,
        ];

        yield 'with_sampled_trace' => [
            ExemplarFilterEnum::WithSampledTrace->value,
            WithSampledTraceExemplarFilter::class,
        ];

        yield 'unknown' => [
            'unknown',
            null,
        ];
    }
}
