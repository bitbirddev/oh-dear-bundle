<?php

declare(strict_types=1);

namespace bitbirddev\OhDearBundle\Store;

use DateTimeImmutable;
use OhDear\HealthCheckResults\CheckResult;

use function time;

final class StoredResult
{
    public readonly DateTimeImmutable $createdAt;

    public function __construct(
        public readonly string $identifier,
        public readonly CheckResult $checkResult,
    ) {
        $this->createdAt = new DateTimeImmutable();
    }

    public function isExpired(int $frequency, int $threshold): bool
    {
        return ($this->createdAt->getTimestamp() + $frequency + $threshold) < time();
    }
}
