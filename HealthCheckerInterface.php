<?php

declare(strict_types=1);

namespace bitbirddev\OhDearBundle;

use bitbirddev\OhDearBundle\Checks\CheckInterface;
use OhDear\HealthCheckResults\CheckResults;

interface HealthCheckerInterface
{
    public function addHealthChecker(CheckInterface $checker): void;

    public function fetchLatestCheckResults(): CheckResults;

    public function runAllChecks(): CheckResults;

    public function runAllChecksAndStore(bool $omitCache): void;
}
