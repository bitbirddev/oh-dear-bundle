<?php

namespace bitbirddev\OhDearBundle\Checks;

use bitbirddev\OhDearBundle\Checks\CheckInterface;
use OhDear\HealthCheckResults\CheckResult;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class EnvironmentCheck implements CheckInterface
{
    protected string $expected = 'prod';

    public function __construct(
        protected ContainerInterface $container
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

    public function expectedToBe(string $env): self
    {
        $this->expected = $env;

        return $this;
    }

    public function runCheck(): CheckResult
    {
        $actual = $this->container->getParameter('kernel.environment');

        $result = new CheckResult(
            name: $this->identify(),
            label: 'Environment',
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
                ->notificationMessage("The Environment was expected to be `{$this->expected}`, but actually was `{$actual}`");

        return $result;
    }
}
