<?php

declare(strict_types=1);

namespace bitbirddev\OhDearBundle\Command;

use bitbirddev\OhDearBundle\Contracts\HealthCheckerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'health:check',
    description: 'Run all health checks',
)]
final class HealthCheckCommand extends Command
{
    public function __construct(
        private readonly HealthCheckerInterface $healthChecker,
    ) {
        parent::__construct();
    }

    public function configure(): void
    {
        $this->addOption('omit-cache', 'c', InputOption::VALUE_NONE, 'Omit cache and run all checks');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $omitCache = $input->getOption('omit-cache');

        $this->healthChecker->runAllChecksAndStore((bool) $omitCache);

        return Command::SUCCESS;
    }
}
