<?php

declare(strict_types=1);

namespace bitbirddev\OhDearBundle\Checks;

use OhDear\HealthCheckResults\CheckResult;
use Symfony\Component\HttpKernel\KernelInterface;

final class OptimizedAppCheck implements CheckInterface
{
    public function __construct(
        protected KernelInterface $kernel,
        protected bool $expected = true,
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

    public function runCheck(): CheckResult
    {
        // Get the project directory
        $projectDir = $this->kernel->getProjectDir();

        // Check if the .env.local.php file exists
        $compiledEnvFilePath = $projectDir.'/.env.local.php';

        $actual = file_exists($compiledEnvFilePath);

        $result = new CheckResult(
            name: $this->identify(),
            label: 'Optimized App',
            shortSummary: $this->convertToWord($actual),
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

    protected function convertToWord(bool $boolean): string
    {
        return $boolean ? 'true' : 'false';
    }
}
