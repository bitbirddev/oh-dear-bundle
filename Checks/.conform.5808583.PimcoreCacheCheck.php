<?php

declare(strict_types=1);

namespace bitbirddev\OhDearBundle\Checks;

use OhDear\HealthCheckResults\CheckResult;

final class PimcoreCacheCheck implements CheckInterface
{
    public function identify(): string
    {
        return 'Pimcore-Cache';
    }

    public function frequency(): int
    {
        return 0;
    }

    public function runCheck(): CheckResult
    {
        $result = new CheckResult(
            name: $this->identify(),
            label: 'Pimcore-Cache',
            shortSummary: 'connected',
        );

        return $this->canWriteValuesToPimcoreCache()
            ? $result->status(CheckResult::STATUS_OK)
            : $result->status(CheckResult::STATUS_FAILED)->shortSummary('error')->notificationMessage('Can not write to cache.');

        return $result;
    }

    protected function canWriteValuesToPimcoreCache(): bool
    {
        $bytes = random_bytes(5);
        $expectedValue = bin2hex($bytes);

        $cacheName = "ohdear-health-check-pimcore-{$expectedValue}";

        \Pimcore\Cache::save(data: $expectedValue, key: $cacheName, lifetime: 10, force: true);
        $actualValue = \Pimcore\Cache::load($cacheName);
        \Pimcore\Cache::clearTag($cacheName);

        return $actualValue === $expectedValue;
    }
}
