<?php

declare(strict_types=1);

namespace bitbirddev\OhDearBundle\Checks;

use OhDear\HealthCheckResults\CheckResult;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

final class SymfonyCacheCheck implements CheckInterface
{
    public function __construct(
        protected CacheInterface $cache
    ) {
    }

    public function identify(): string
    {
        return 'Symfony-Cache';
    }

    public function frequency(): int
    {
        return 0;
    }

    public function runCheck(): CheckResult
    {
        $result = new CheckResult(
            name: $this->identify(),
            label: 'Symfony-Cache',
            shortSummary: 'connected',
        );

        return $this->canWriteValuesToSymfonyCache()
            ? $result->status(CheckResult::STATUS_OK)
            : $result->status(CheckResult::STATUS_FAILED)->shortSummary('error')->notificationMessage('Can not write to cache.');

        return $result;
    }

    protected function canWriteValuesToSymfonyCache(): bool
    {
        $bytes = random_bytes(5);
        $string = bin2hex($bytes);
        $expectedValue = $string;
        $cacheName = "ohdear-health-check-symfony-{$expectedValue}";

        $this->cache->get($cacheName, static function (ItemInterface $item) use ($expectedValue) {
            $item->expiresAfter(10);

            return $expectedValue;
        });

        $cachedValue = $this->cache->get($cacheName, static function (ItemInterface $item) {
            return $item->get();
        });

        return $expectedValue === $cachedValue;
    }
}
