<?php

namespace bitbirddev\OhDearBundle\Checks;

use bitbirddev\OhDearBundle\Checks\CheckInterface;
use OhDear\HealthCheckResults\CheckResult;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class OptimizedAppCheck implements CheckInterface
{
    protected bool $expected = true;

    public function __construct(
        protected ContainerInterface $container
    ) {
    }

    public function identify(): string
    {
        return 'Optimized App';
    }

    public function frequency(): int
    {
        return 0;
    }

    public function expectedToBe(string $env): self
    {
        $this->expected = $env;

        return $this;
    }

    public function runCheck(): CheckResult
    {
        // Get the project directory
        $projectDir = $this->container->getParameter('kernel.project_dir');

        // Check if the .env.local.php file exists
        $compiledEnvFilePath = $projectDir.'/.env.local.php';

        $actual = file_exists($compiledEnvFilePath);

        $result = new CheckResult(
            name: $this->identify(),
            label: 'Optimized App',
            shortSummary: $actual,
            // status: CheckResult::STATUS_OK,
            meta: [
                'expected' => $this->expected,
                'actual' => $actual,
            ]
        );

        return $this->expected === $actual
            ? $result->status(CheckResult::STATUS_OK)
            : $result->status(CheckResult::STATUS_FAILED)
                ->notificationMessage('The `composer dump-env` command did not run.');

        return $result;
    }
}
