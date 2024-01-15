<?php

declare(strict_types=1);

namespace bitbirddev\OhDearBundle\Checks;

use bitbirddev\OhDearBundle\CheckResult;
use Composer\Semver\Semver;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\KernelInterface;
use bitbirddev\OhDearBundle\Contracts\CheckInterface;

final class SymfonyVersionCheck implements CheckInterface
{
    protected string $expected = '^6';

    public function __construct(
        protected KernelInterface $kernel,
    ) {
    }

    public function identify(): string
    {
        return 'Symfony Version';
    }

    public function frequency(): int
    {
        return 0;
    }

    public function runCheck(): CheckResult
    {
        $actual = Kernel::VERSION;

        $result = CheckResult::make(
            name: $this->identify(),
            label: 'Symfony Version',
            shortSummary: $actual,
            meta: [
                'expected' => $this->expected,
                'actual' => $actual,
            ]
        );

        return Semver::satisfies($actual, $this->expected)
            ? $result->ok()
            : $result->failed("The SymfonyVersion was expected to be `{$this->expected}`, but actually was `{$actual}`");

        return $result;
    }

    public function expect(string $value): self
    {
        $this->expected = $value;

        return $this;
    }
}
