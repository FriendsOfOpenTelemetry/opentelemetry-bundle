<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Unit\Command;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Application\Command\DummyCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @coversDefaultClass \FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Application\Command\DummyCommand
 */
final class DummyCommandTest extends TestCase
{
    public function testSuccessful(): void
    {
        $commandTester = new CommandTester(new DummyCommand());

        $commandTester->execute([]);

        self::assertSame(
            "Running dummy command.\nDone.\n",
            $commandTester->getDisplay(),
        );
        $commandTester->assertCommandIsSuccessful();
    }

    public function testFail(): void
    {
        $commandTester = new CommandTester(new DummyCommand());

        $exitCode = $commandTester->execute(['--fail' => true]);

        self::assertSame(
            "Running dummy command.\nSomething went wrong.\n",
            $commandTester->getDisplay(),
        );
        self::assertSame(Command::FAILURE, $exitCode);
    }

    public function testThrow(): void
    {
        $commandTester = new CommandTester(new DummyCommand());

        $this->expectExceptionObject(new \RuntimeException('Oops'));
        $commandTester->execute(['--throw' => true]);
    }

    /**
     * @return array<int, array{0: int, 1: array<string, mixed>}>
     */
    public function exitCodeProvider(): array
    {
        return [
            [42, ['--exit-code' => 42]],
            [42, ['--exit-code' => 42, '--fail' => true]],
        ];
    }

    /**
     * @dataProvider exitCodeProvider
     *
     * @param array<string, mixed> $args
     */
    public function testExitCode(int $exitCode, array $args): void
    {
        $commandTester = new CommandTester(new DummyCommand());

        self::assertSame($exitCode, $commandTester->execute($args));
    }
}
