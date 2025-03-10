<?php

namespace App\Command\Observable;

use OpenTelemetry\API\Metrics\MeterInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('observable:manual-command')]
class ManualCommand extends Command
{
    public function __construct(
        private readonly MeterInterface $meter,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $counter = $this->meter->createCounter('manual');

        $counter->add(1);

        return Command::SUCCESS;
    }
}
