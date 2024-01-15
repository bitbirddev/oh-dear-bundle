<?php

declare(strict_types=1);

namespace bitbirddev\OhDearBundle\Checks;

use bitbirddev\OhDearBundle\CheckResult;
use Composer\Semver\Semver;
use Symfony\Component\HttpKernel\KernelInterface;
use bitbirddev\OhDearBundle\Contracts\CheckInterface;

final class PimcoreVersionCheck implements CheckInterface
{
    protected string $expected = '^11';

    public function __construct(
        protected KernelInterface $kernel,
    ) {
    }

    public function identify(): string
    {
        return 'Pimcore Version';
    }

    public function frequency(): int
    {
        return 0;
    }

    public function runCheck(): CheckResult
    {
        $actual = \Pimcore\Version::getVersion();

        $result = CheckResult::make(
            name: $this->identify(),
            label: 'Pimcore Version',
            shortSummary: $actual,
            meta: [
                'expected' => $this->expected,
                'actual' => $actual,
            ]
        );

        return Semver::satisfies($actual, $this->expected)
            ? $result->ok()
            : $result->failed("The Pimcore Version was expected to be `{$this->expected}`, but actually was `{$actual}`");

        return $result;
    }

    public function expect(string $value): self
    {
        $this->expected = $value;

        return $this;
    }
}
