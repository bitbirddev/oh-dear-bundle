<?php

declare(strict_types=1);

namespace bitbirddev\OhDearBundle\Checks;

use bitbirddev\OhDearBundle\CheckResult;
use Symfony\Component\HttpKernel\KernelInterface;
use bitbirddev\OhDearBundle\Contracts\CheckInterface;

final class DebugModeCheck implements CheckInterface
{
    protected bool $expected = false;

    public function __construct(
        protected KernelInterface $kernel,
    ) {
    }

    public function identify(): string
    {
        return 'Debug Mode';
    }

    public function frequency(): int
    {
        return 0;
    }

    public function runCheck(): CheckResult
    {
        $actual = $this->kernel->isDebug();

        $result = CheckResult::make(
            name: $this->identify(),
            label: 'Debug Mode',
            shortSummary: $this->convertToWord($actual),
            meta: [
                'expected' => $this->expected,
                'actual' => $actual,
            ]
        );

        return $this->expected === $actual
            ? $result->ok()
            : $result->failed("The debug mode was expected to be `{$this->convertToWord((bool) $this->expected)}`, but actually was `{$this->convertToWord((bool) $actual)}`");

        return $result;
    }

    protected function convertToWord(bool $boolean): string
    {
        return $boolean ? 'enabled' : 'disabled';
    }

    public function expect(bool $value): self
    {
        $this->expected = $value;

        return $this;
    }
}
