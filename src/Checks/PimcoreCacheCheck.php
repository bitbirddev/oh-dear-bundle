<?php

declare(strict_types=1);

namespace bitbirddev\OhDearBundle\Checks;

use bitbirddev\OhDearBundle\CheckResult;
use bitbirddev\OhDearBundle\Contracts\CheckInterface;

final class PimcoreCacheCheck implements CheckInterface
{
    public function identify(): string
    {
        return 'Pimcore Cache';
    }

    public function frequency(): int
    {
        return 0;
    }

    public function runCheck(): CheckResult
    {
        $result = CheckResult::make(
            name: $this->identify(),
            label: 'Pimcore Cache',
            shortSummary: 'connected',
        );

        return $this->canWriteValuesToPimcoreCache()
            ? $result->ok()
            : $result->shortSummary('error')->failed('Can not write to cache.');

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
