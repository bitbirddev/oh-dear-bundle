<?php

declare(strict_types=1);

namespace bitbirddev\OhDearBundle\Checks;

use bitbirddev\OhDearBundle\CheckResult;
use Symfony\Component\HttpKernel\KernelInterface;
use bitbirddev\OhDearBundle\Contracts\CheckInterface;

final class EnvironmentCheck implements CheckInterface
{
    protected string $expected = 'prod';

    public function __construct(
        protected KernelInterface $kernel,
    ) {
    }

    public function identify(): string
    {
        return 'Environment';
    }

    public function frequency(): int
    {
        return 0;
    }

    public function runCheck(): CheckResult
    {
        $actual = $this->kernel->getEnvironment();

        $result = CheckResult::make(
            name: $this->identify(),
            label: 'Environment',
            shortSummary: $actual,
            meta: [
                'expected' => $this->expected,
                'actual' => $actual,
            ]
        );

        return $this->expected === $actual
            ? $result->ok()
            : $result->failed("The Environment was expected to be `{$this->expected}`, but actually was `{$actual}`");

        return $result;
    }

    public function expect(string $value): self
    {
        $this->expected = $value;

        return $this;
    }
}
