<?php

namespace bitbirddev\OhDearBundle\Checks;

use bitbirddev\OhDearBundle\Checks\CheckInterface;
use OhDear\HealthCheckResults\CheckResult;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class DebugModeCheck implements CheckInterface
{
    protected bool $expected = false;

    public function __construct(
        protected ContainerInterface $container
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

    public function expectedToBe(bool $bool): self
    {
        $this->expected = $bool;

        return $this;
    }

    public function runCheck(): CheckResult
    {
        $actual = $this->container->getParameter('kernel.debug');

        $result = new CheckResult(
            name: $this->identify(),
            label: 'Debug Mode',
            shortSummary: $this->convertToWord($actual),
            // status: CheckResult::STATUS_OK,
            meta: [
                'expected' => $this->expected,
                'actual' => $actual,
            ]
        );

        return $this->expected === $actual
            ? $result->status(CheckResult::STATUS_OK)
            : $result->status(CheckResult::STATUS_FAILED)->notificationMessage("The debug mode was expected to be `{$this->convertToWord((bool) $this->expected)}`, but actually was `{$this->convertToWord((bool) $actual)}`");

        return $result;
    }

    protected function convertToWord(bool $boolean): string
    {
        return $boolean ? 'on' : 'off';
    }
}
