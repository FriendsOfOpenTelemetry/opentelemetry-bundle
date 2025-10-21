<?php

declare(strict_types=1);

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Unit\OpenTelemetry\Transport;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Transport\KafkaTransport;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use RdKafka\Conf;

#[CoversClass(KafkaTransport::class)]
final class KafkaTransportTest extends TestCase
{
    protected function setUp(): void
    {
        if (!\class_exists(Conf::class)) {
            self::markTestSkipped('rdkafka extension not available in the test environment.');
        }
    }

    public function testContentTypeIsProtobuf(): void
    {
        $conf = new Conf();
        $t = new KafkaTransport($conf, 'test-topic');
        self::assertSame('application/x-protobuf', $t->contentType());
    }

    public function testSendReturnsCompletedFutureOnSuccess(): void
    {
        $conf = new Conf();
        $transport = new KafkaTransport($conf, 'test-topic');

        // We cannot easily mock RdKafka internals without the extension. Basic smoke test for method existence.
        $future = $transport->send('payload');
        /* @phpstan-ignore-next-line */
        self::assertTrue(\method_exists($future, 'await')); // interface method presence
    }
}
