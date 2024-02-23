<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Application\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('dummy-command')]
final class DummyCommand extends Command
{
    protected function configure(): void
    {
        $this->addOption('fail', 'f', InputOption::VALUE_OPTIONAL, 'Make the command fail');
        $this->addOption('throw', 't', InputOption::VALUE_OPTIONAL, 'Make the command trow');
        $this->addOption('exit-code', 'c', InputOption::VALUE_OPTIONAL, 'Set a custom exit code');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $shouldFail = $input->getOption('fail');
        $shouldThrow = $input->getOption('throw');
        $exitCode = $input->getOption('exit-code');

        $output->writeln('Running dummy command.');

        if (null !== $shouldFail) {
            $output->writeln('Something went wrong.');

            return $exitCode ?? Command::FAILURE;
        }

        if (null !== $shouldThrow) {
            throw new \RuntimeException('Oops');
        }

        $output->writeln('Done.');

        return $exitCode ?? Command::SUCCESS;
    }
}
