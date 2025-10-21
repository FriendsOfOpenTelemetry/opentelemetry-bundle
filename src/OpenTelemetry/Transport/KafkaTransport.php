<?php

declare(strict_types=1);

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Transport;

use OpenTelemetry\SDK\Common\Export\TransportInterface;
use OpenTelemetry\SDK\Common\Future\CancellationInterface;
use OpenTelemetry\SDK\Common\Future\CompletedFuture;
use OpenTelemetry\SDK\Common\Future\ErrorFuture;
use OpenTelemetry\SDK\Common\Future\FutureInterface;
use RdKafka\Conf;
use RdKafka\Producer;
use RdKafka\ProducerTopic;

/**
 * @template-implements TransportInterface<"application/x-protobuf">
 */
final readonly class KafkaTransport implements TransportInterface
{
    private const FLUSH_TIMEOUT = 10000;

    private Producer $producer;
    private ProducerTopic $topicHandle;

    public function __construct(
        Conf $configuration,
        string $topic,
    ) {
        if (!\class_exists(Conf::class)) {
            throw new \RuntimeException('The PHP extension "rdkafka" is required to use the Kafka transport.');
        }

        $this->producer = new Producer($configuration);
        $this->topicHandle = $this->producer->newTopic($topic);
    }

    public function contentType(): string
    {
        return 'application/x-protobuf';
    }

    /**
     * @phpstan-return FutureInterface<null>
     */
    public function send(string $payload, ?CancellationInterface $cancellation = null): FutureInterface
    {
        try {
            $this->topicHandle->producev(\RD_KAFKA_PARTITION_UA, 0, $payload);
        } catch (\Throwable $exception) {
            return new ErrorFuture($exception);
        }

        return new CompletedFuture(null);
    }

    public function shutdown(?CancellationInterface $cancellation = null): bool
    {
        return $this->flushInternal();
    }

    public function forceFlush(?CancellationInterface $cancellation = null): bool
    {
        return $this->flushInternal();
    }

    private function flushInternal(): bool
    {
        // librdkafka recommends retrying the flush operation a couple of times when it returns a null result.
        $timeout = self::FLUSH_TIMEOUT;
        $start = \microtime(true);
        do {
            $res = $this->producer->flush($timeout);
            if (\RD_KAFKA_RESP_ERR_NO_ERROR === $res) {
                return true;
            }

            // reduce timeout
            $elapsedMs = (int) \round((\microtime(true) - $start) * 1000);
            $timeout = \max(0, self::FLUSH_TIMEOUT - $elapsedMs);
        } while ($timeout > 0);

        return false;
    }
}
