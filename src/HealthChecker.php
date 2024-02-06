<?php

declare(strict_types=1);

namespace bitbirddev\OhDearBundle;

use bitbirddev\OhDearBundle\Contracts\CheckInterface;
use bitbirddev\OhDearBundle\Contracts\HealthCheckerInterface;
use bitbirddev\OhDearBundle\Store\StoreInterface;
use bitbirddev\OhDearBundle\Store\StoreItem;
use DateTimeImmutable;
use OhDear\HealthCheckResults\CheckResult;
use OhDear\HealthCheckResults\CheckResults;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

final class HealthChecker implements HealthCheckerInterface
{
    /** @var array<CheckInterface> */
    private array $checks = [];

    public function __construct(
        private readonly StoreInterface $store,

        #[Autowire(param: 'oh_dear.expiration_threshold')]
        private readonly int $expirationThreshold,

        #[TaggedIterator(tag: 'oh_dear.health_check_provider')]
        private iterable $providers = [],
    ) {
        foreach ($this->providers as $provider) {
            foreach ($provider->getHealthChecks() as $check) {
                $this->addHealthChecker($check);
            }
        }
    }

    public function addHealthChecker(CheckInterface $checker): void
    {
        $this->checks[] = $checker;
    }

    public function fetchLatestCheckResults(): CheckResults
    {
        $checkResults = new CheckResults(
            new DateTimeImmutable()
        );

        foreach ($this->checks as $checker) {
            $lastResult = null;
            $result = null;

            if (0 < $checker->frequency()) {
                $lastResult = $this->store->fetchLastResult($checker->identify());
                $result = $lastResult?->checkResult;
            }

            if (null === $result) {
                $result = $checker->runCheck();
                $this->store->save(
                    $checker->identify(),
                    new StoreItem(
                        $checker->identify(),
                        $result
                    )
                );
            }

            if (true === ($lastResult?->isExpired($checker->frequency(), $this->expirationThreshold) ?? false)) {
                $result->status = CheckResult::STATUS_WARNING;
                $result->meta = [
                    ...$result->meta,
                    "Last check was more than {$checker->frequency()} seconds ago",
                ];
            }

            $checkResults->addCheckResult($result);
        }

        return $checkResults;
    }

    public function runAllChecks(): CheckResults
    {
        $checkResults = new CheckResults(
            new DateTimeImmutable()
        );

        foreach ($this->checks as $checker) {
            $checkResults->addCheckResult($checker->runCheck());
        }

        return $checkResults;
    }

    public function runAllChecksAndStore(bool $omitCache = false): void
    {
        foreach ($this->checks as $checker) {
            $lastResult = $this->store->fetchLastResult($checker->identify());

            if (false === $omitCache && null !== $lastResult && false === $lastResult->isExpired($checker->frequency(), 0)) {
                continue;
            }

            $result = $checker->runCheck();

            $this->store->save(
                $checker->identify(),
                new StoreItem(
                    $checker->identify(),
                    $result
                )
            );
        }
    }
}
