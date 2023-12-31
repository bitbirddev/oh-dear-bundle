<?php

declare(strict_types=1);

namespace bitbirddev\OhDearBundle\Checks;

use OhDear\HealthCheckResults\CheckResult;
use Symfony\Component\HttpKernel\KernelInterface;

final class EnvironmentCheck implements CheckInterface
{
    public function __construct(
        protected KernelInterface $kernel,
        protected string $expected = 'prod'
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

        $result = new CheckResult(
            name: $this->identify(),
            label: 'Environment',
            shortSummary: $actual,
            meta: [
                'expected' => $this->expected,
                'actual' => $actual,
            ]
        );

        return $this->expected === $actual
            ? $result->status(CheckResult::STATUS_OK)
            : $result->status(CheckResult::STATUS_FAILED)
                ->notificationMessage("The Environment was expected to be `{$this->expected}`, but actually was `{$actual}`");

        return $result;
    }
}
