<?php

declare(strict_types=1);

namespace bitbirddev\OhDearBundle\Checks;

use OhDear\HealthCheckResults\CheckResult;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class DebugModeCheck implements CheckInterface
{
    public function __construct(
        protected ContainerInterface $container,
        protected bool $expected = false,
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
        $actual = $this->container->getParameter('kernel.debug');

        $result = new CheckResult(
            name: $this->identify(),
            label: 'Debug Mode',
            shortSummary: $this->convertToWord($actual),
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
