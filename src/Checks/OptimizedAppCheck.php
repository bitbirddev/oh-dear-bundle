<?php

declare(strict_types=1);

namespace bitbirddev\OhDearBundle\Checks;

use bitbirddev\OhDearBundle\CheckResult;
use Symfony\Component\HttpKernel\KernelInterface;
use bitbirddev\OhDearBundle\Contracts\CheckInterface;

final class OptimizedAppCheck implements CheckInterface
{
    protected bool $expected = true;

    public function __construct(
        protected KernelInterface $kernel,
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

        $result = CheckResult::make(
            name: $this->identify(),
            label: 'Optimized App',
            shortSummary: $this->convertToWord($actual),
            meta: [
                'expected' => $this->expected,
                'actual' => $actual,
            ]
        );

        return $this->expected === $actual
            ? $result->ok()
            : $result->failed('The `composer dump-env` command did not run.');

        return $result;
    }

    protected function convertToWord(bool $boolean): string
    {
        return $boolean ? 'true' : 'false';
    }

    public function expect(bool $value): self
    {
        $this->expected = $value;

        return $this;
    }
}
